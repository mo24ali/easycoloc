<?php

namespace App\Http\Controllers;

use App\Models\Collocation;
use App\Models\User;
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

        if ($user->isOwner()) {
            $collocations = $user->ownedCollocations()
                ->withCount('members')
                ->orderByDesc('created_at')
                ->paginate(9);
        } else {
            $collocations = $user->collocations()
                ->withCount('members')
                ->orderByDesc('created_at')
                ->paginate(9);
        }

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
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $collocation = Collocation::create([
            'name' => $validated['name'],
            'owner_id' => Auth::id(),
            'status' => 'active',
        ]);

        return redirect()->route('collocation.show', $collocation)
            ->with('status', "{$collocation->name} has been created!");
    }

    /**
     * Display a specific collocation.
     */
    public function show(Collocation $collocation): View
    {
        $this->authorize('view', $collocation);
        $collocation->load(['owner', 'members']);

        // Get detailed expense share information
        $expenseShares = $collocation->getExpenseShareDetails();

        return view('collocation.show', compact('collocation', 'expenseShares'));
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
    public function update(Request $request, Collocation $collocation): RedirectResponse
    {
        $this->authorize('update', $collocation);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'status' => ['required', 'in:active,inactive'],
        ]);

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
    public function removeMember(Collocation $collocation, User $user): RedirectResponse
    {
        $this->authorize('update', $collocation);

        if ($user->id === $collocation->owner_id) {
            return back()->withErrors(['member' => 'Cannot remove the collocation owner.']);
        }

        $collocation->members()->updateExistingPivot($user->id, ['left_at' => now()]);

        return redirect()->route('collocation.members', $collocation)
            ->with('status', "{$user->name} has been removed from the collocation.");
    }

    /**
     * Allow the authenticated member to leave the collocation.
     */
    public function leave(Collocation $collocation): RedirectResponse
    {
        $user = Auth::user();

        // Owner cannot leave while other members are still present
        if ($collocation->owner_id === $user->id) {
            $otherMembers = $collocation->members()
                ->wherePivotNull('left_at')
                ->where('users.id', '!=', $user->id)
                ->count();

            if ($otherMembers > 0) {
                return back()->withErrors([
                    'leave' => 'You cannot leave while there are still members. Remove them first or transfer ownership.',
                ]);
            }
        }

        $collocation->members()->updateExistingPivot($user->id, ['left_at' => now()]);

        return redirect()->route('dashboard')
            ->with('status', "You have left {$collocation->name}.");
    }
}
