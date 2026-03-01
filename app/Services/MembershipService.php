<?php

namespace App\Services;

use App\Models\Collocation;
use App\Models\User;
use Illuminate\Validation\ValidationException;

/**
 * Service to manage collocation memberships.
 */
class MembershipService
{
    /**
     * Check if a user already has an active collocation.
     */
    public function userHasActiveCollocation(User $user): bool
    {
        return $user->collocations()
            ->wherePivotNull('left_at')
            ->where('status', 'active')
            ->exists();
    }

    /**
     * Retrieve the user's active collocation (if any).
     */
    public function getUserActiveCollocation(User $user): ?Collocation
    {
        return $user->collocations()
            ->wherePivotNull('left_at')
            ->where('status', 'active')
            ->first();
    }

    /**
     * Validate that the user can join or create a new collocation.
     */
    public function validateCanJoinCollocation(User $user, string $context = 'join'): void
    {
        if ($this->userHasActiveCollocation($user)) {
            $activeCollocation = $this->getUserActiveCollocation($user);

            throw ValidationException::withMessages([
                'collocation' => "You already have an active collocation: '{$activeCollocation->name}'. "
                    . "Leave or end it before joining or creating another one.",
            ]);
        }
    }

    /**
     * Validate that the user can create a new collocation.
     */
    public function validateCanCreateCollocation(User $user): void
    {
        $this->validateCanJoinCollocation($user, 'create');
    }


}
