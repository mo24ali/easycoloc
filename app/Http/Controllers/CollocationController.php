<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCollocationRequest;
use App\Http\Requests\UpdateCollocationRequest;
use App\Models\Collocation;
use App\Models\User;
use App\Services\MembershipService;
use App\Services\CollocationCleanupService;
use App\Services\DebtOptimizationService;
use App\Services\CollocationService;
use App\Services\BalanceService;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class CollocationController extends Controller
{
    /**
     * List collocations the authenticated user owns or belongs to.
     */
    public function index(): View
    {
        $user = Auth::user();

        // Use union to combine owned and member collocations in a single query
        $ownedQuery = $user->ownedCollocations()
            ->select(['collocations.*'])
            ->withCount('members');


        // Get the member who are in the collocation and havent left yet
        $memberQuery = Collocation::whereHas('members', function ($query) use ($user): void {
            $query->where('user_id', $user->id)->whereNull(columns: 'left_at');
        })
            ->select(['collocations.*'])
            ->withCount('members');

        // Used the union to get the collocation wethr if the current user is the owner / or a member
        $collocations = $ownedQuery->union($memberQuery)
            ->orderByDesc('created_at')
            ->paginate(9);

        return view('collocation.index', compact('collocations'));
    }

    /**
     * Show the form for creating a new collocation (owner only).
     */
    public function create(): View
    {
        return view('collocation.create');
    }

    /**
     * Store a newly created collocation.
     */
    public function store(StoreCollocationRequest $request, MembershipService $membershipService): RedirectResponse
    {
        $validated = $request->validated();

        $user = Auth::user();

        $membershipService->validateCanCreateCollocation($user);

        if ($user->isUser()) {
            $user->update(['role' => 'owner']);
            $user->refresh();
        }

        $collocation = Collocation::create([
            'name' => $validated['name'],
            'owner_id' => $user->id,
            'status' => 'active',
        ]);

        // Add owner as a member of the collocation
        $collocation->members()->attach($user->id, [
            'role' => 'owner',
            'joined_at' => now(),
        ]);

        return redirect()->route('collocation.show', $collocation)
            ->with('status', "{$collocation->name} has been created!");
    }

    /**
     * Display a specific collocation.
     */
    public function show(Collocation $collocation, DebtOptimizationService $debtOptimizationService, BalanceService $balanceService): View
    {
        $this->authorize('view', $collocation);

        // Get detailed expense share information
        $expenseShares = $collocation->getExpenseShareDetails();


        $optimizedTransactions = $debtOptimizationService->getOptimizedTransactions($collocation, $balanceService);

        return view('collocation.show', compact('collocation', 'expenseShares', 'optimizedTransactions'));
    }

    /**
     * Show the edit form (owner only).
     */
    public function edit(Collocation $collocation): View
    {
        $this->authorize('update', $collocation);
        return view('collocation.edit', compact('collocation'));
    }

    /**
     * Update the collocation name / status.
     */
    public function update(UpdateCollocationRequest $request, Collocation $collocation): RedirectResponse
    {
        $validated = $request->validated();
        $collocation->update($validated);

        return redirect()->route('collocation.show', $collocation)
            ->with('status', 'Collocation updated successfully.');
    }

    /**
     * Cancel the collocation (sets cancelled_at).
     */
    public function cancel(Collocation $collocation): RedirectResponse
    {
        $this->authorize('cancel', $collocation);
        $collocation->cancel();

        return redirect()->route('collocation.show', $collocation)
            ->with('status', 'Collocation has been cancelled.');
    }

    /**
     * Paginated member list for a collocation.
     */
    public function members(Collocation $collocation): View
    {
        $this->authorize('viewMembers', $collocation);

        $members = $collocation->members()
            ->wherePivotNull('left_at')
            ->orderBy('name')
            ->paginate(10);

        return view('collocation.members', compact('collocation', 'members'));
    }

    /**
     * Remove a specific member from the collocation (owner only).
     */
    public function removeMember(Collocation $collocation, User $user, CollocationCleanupService $cleanupService): RedirectResponse
    {
        $this->authorize('update', $collocation);

        if ($user->id === $collocation->owner_id) {
            return back()->withErrors(['member' => 'Cannot remove the collocation owner.']);
        }

        $details = $cleanupService->ownerRemovesMember($collocation, $user);

        return redirect()->route('collocation.members', $collocation)
            ->with('status', $details['message']);
    }

    /**
     * Pass ownership from current owner to another member.
     */
    public function passOwnership(Collocation $collocation, User $user, CollocationService $collocationService): RedirectResponse
    {
        $this->authorize('update', $collocation);

        try {
            $collocationService->passOwnership($collocation, $user);
        } catch (Exception $e) {
            return back()->withErrors(['member' => $e->getMessage()]);
        }

        return redirect()->route('collocation.members', $collocation)
            ->with('status', "Ownership transferred to {$user->name} successfully.");
    }

    /**
     * Allow the authenticated member to leave the collocation.
     */
    public function leave(Collocation $collocation, CollocationCleanupService $cleanupService, CollocationService $collocationService): RedirectResponse
    {
        $user = Auth::user();

        // Owner cannot leave at all, ownership must be transferred first
        if ($collocation->owner_id === $user->id && !$user->isAdmin()) {
            return back()->withErrors([
                'leave' => 'You cannot leave while you are the owner. Transfer ownership first.',
            ]);
        }


        if ($collocation->owner_id === $user->id) {
            return back()->withErrors([
                'leave' => 'You cannot leave while you are the owner.
                Transfer ownership or cancel the collocation.',
            ]);
        }

        $details = $cleanupService->memberLeaves($collocation, $user);

        // Delegate structural identity finalization checking to CollocationService
        $collocationService->completeMemberLeave($user);

        return redirect()->route('dashboard')
            ->with('status', "You have left {$collocation->name}. (Reputation: {$details['reputation_change']})");
    }
}
