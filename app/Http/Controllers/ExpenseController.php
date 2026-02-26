<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Collocation;
use App\Models\Expense;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
    public function store(Request $request, Collocation $collocation): RedirectResponse
    {
        $this->authorize('view', $collocation);

        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01'],
            'title' => ['required', 'string', 'max:255'],
            'category_id' => [
                'required',
                'string',
                function ($attribute, $value, $fail) {
                    if (empty($value)) {
                        $fail('Please select a category.');
                    } elseif ($value !== 'new') {
                        // It should be a numeric ID that exists
                        if (!is_numeric($value) || !Category::where('id', (int) $value)->exists()) {
                            $fail('The selected category is invalid.');
                        }
                    }
                },
            ],
            'new_category' => ['required_if:category_id,new', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:500'],
            'expense_date' => ['required', 'date'],
        ]);

        if ($validated['category_id'] === 'new') {
            $category = Category::firstOrCreate([
                'name' => $request->new_category
            ]);
            $validated['category_id'] = $category->id;
        } else {
            $validated['category_id'] = (int) $validated['category_id'];
        }

        $collocation->expenses()->create([
            ...$validated,
            'member_id' => Auth::id(),
        ]);

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
    public function update(Request $request, Expense $expense): RedirectResponse
    {
        $this->authorize('update', $expense);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'description' => ['nullable', 'string', 'max:500'],
            'expense_date' => ['required', 'date'],
        ]);

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
     * Dashboard: user's recent expenses across all their collocations.
     */
    public function getExpensePerUser(): View
    {
        $user = Auth::user();
        $collocations = collect();

        if ($user->isOwner()) {
            $collocations = $user->ownedCollocations()->active()->withCount('members')->get();
        } elseif ($user->isMember()) {
            $collocations = $user->collocations()->active()->withCount('members')->get();
        }

        $recentExpenses = Expense::with(['collocation', 'category'])
            ->where('member_id', $user->id)
            ->orderByDesc('expense_date')
            ->take(5)
            ->get();

        return view('dashboard', compact('collocations', 'recentExpenses'));
    }
}
