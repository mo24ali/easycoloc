<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Collocation;
use App\Models\Expense;
use App\Models\ExpenseShare;
use App\Http\Requests\StoreExpenseRequest;
use App\Http\Requests\UpdateExpenseRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ExpenseController extends Controller
{
    /**
     * List expenses for a collocation (member+ required — enforced by route).
     */
    public function index(Collocation $collocation): View
    {
        $this->authorize('view', $collocation);

        $categoryId = request('category');

        $expenses = $collocation->expenses()
            ->with(['member', 'category'])
            ->when($categoryId, fn($q) => $q->where('category_id', $categoryId))
            ->orderByDesc('expense_date')
            ->paginate(20)
            ->withQueryString();

        $categories = Category::orderBy('name')->get();

        return view('expense.index', compact('collocation', 'expenses', 'categories', 'categoryId'));
    }

    /**
     * Show the expense creation form.
     */
    public function create(Collocation $collocation): View
    {
        $this->authorize('view', $collocation);
        $categories = Category::orderBy('name')->get();
        return view('expense.create', compact('collocation', 'categories'));
    }

    /**
     * Store a new expense.
     */
    public function store(StoreExpenseRequest $request, Collocation $collocation): RedirectResponse
    {
        $validated = $request->validated();

        // Wrap expense creation in a transaction to ensure data consistency
        DB::transaction(function () use ($validated, $request, $collocation) {
            if ($validated['category_id'] === 'new') {
                $category = Category::firstOrCreate([
                    'name' => $request->new_category
                ]);
                $validated['category_id'] = $category->id;
            } else {
                $validated['category_id'] = (int) $validated['category_id'];
            }

            // Create the expense
            $expense = $collocation->expenses()->create([
                ...$validated,
                'member_id' => Auth::id(),
            ]);

            // Get all active members in the collocation (including owner) and split the expense
            // Active members are those in collocation_user table with left_at = NULL
            $activeMembers = $collocation->members()->wherePivotNull('left_at')->get();

            // Ensure owner is included in expense split
            $membersToSplit = $activeMembers->pluck('id')->toArray();
            if (!in_array($collocation->owner_id, $membersToSplit)) {
                $membersToSplit[] = $collocation->owner_id;
            }
            $membersToSplit = array_unique($membersToSplit);

            if (count($membersToSplit) > 0) {
                $sharePerUser = $expense->amount / count($membersToSplit);

                // Create expense share record for each member (including owner)
                // The creator already paid their share (they spent the money), so mark theirs as payed
                foreach ($membersToSplit as $memberId) {
                    ExpenseShare::create([
                        'expense_id' => $expense->id,
                        'payer_id' => $memberId,
                        'share_per_user' => $sharePerUser,
                        'payed' => $memberId === Auth::id(), // creator already paid their portion
                    ]);
                }
            }
        });

        return redirect()->route('expense.index', $collocation)
            ->with('status', 'Expense added successfully.');
    }

    /**
     * Show the edit form (author or collocation owner).
     */
    public function edit(Expense $expense): View
    {
        $this->authorize('update', $expense);
        $categories = Category::orderBy('name')->get();
        return view('expense.edit', compact('expense', 'categories'));
    }

    /**
     * Update an expense.
     */
    public function update(UpdateExpenseRequest $request, Expense $expense): RedirectResponse
    {
        $validated = $request->validated();
        $expense->update($validated);

        return redirect()->route('expense.index', $expense->collocation_id)
            ->with('status', 'Expense updated.');
    }

    /**
     * Soft-delete an expense (author or collocation owner).
     */
    public function destroy(Expense $expense): RedirectResponse
    {
        $this->authorize('delete', $expense);
        $collocationId = $expense->collocation_id;
        $expense->delete();

        return redirect()->route('expense.index', $collocationId)
            ->with('status', 'Expense deleted.');
    }

    /**
     * Mark a single expense_share as paid directly.
     * Only the payer (debtor) of the share may do this.
     */
    public function markSharePaid(ExpenseShare $share): RedirectResponse
    {
        if ($share->payer_id !== Auth::id()) {
            abort(403, 'You can only mark your own shares as paid.');
        }

        if ($share->payed) {
            return back()->with('status', 'This share was already marked as paid.');
        }

        $share->update(['payed' => true]);

        return back()->with('status', 'Your share has been marked as paid.');
    }

    /**
     * Dashboard: user's recent expenses across all their collocations.
     */
    public function getExpensePerUser(): View
    {
        $user = Auth::user();

        // Get both owned and member collocations
        $ownedQuery = $user->ownedCollocations()
            ->active()
            ->select(['collocations.*'])
            ->withCount('members');

        $memberQuery = Collocation::whereHas('members', function ($query) use ($user) {
            $query->where('user_id', $user->id)->whereNull('left_at');
        })
            ->active()
            ->select(['collocations.*'])
            ->withCount('members');

        $collocations = $ownedQuery->union($memberQuery)->get();

        $recentExpenses = Expense::with(['collocation', 'category'])
            ->where('member_id', $user->id)
            ->orderByDesc('expense_date')
            ->take(5)
            ->get();

        // Dashboard stats
        $userReputation = $user->reputation_score ?? 0;

        // Total of all expenses across all collocations the user belongs to
        $collocationIds = $collocations->pluck('id');
        $globalExpenses = Expense::whereIn('collocation_id', $collocationIds)->sum('amount');

        // Total unpaid shares owed by this user across all collocations
        $globalDebt = \App\Models\ExpenseShare::where('payer_id', $user->id)
            ->where('payed', false)
            ->whereHas('expense', fn($q) => $q->whereIn('collocation_id', $collocationIds))
            ->sum('share_per_user');

        return view('dashboard', compact('collocations', 'recentExpenses', 'userReputation', 'globalExpenses', 'globalDebt'));
    }
}
