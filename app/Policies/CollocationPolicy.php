<?php

namespace App\Policies;

use App\Models\Collocation;
use App\Models\User;

class CollocationPolicy
{
    /**
     * Any authenticated user can list collocations (their own).
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Owner of the collocation or a member of it can view its details.
     */
    public function view(User $user, Collocation $collocation): bool
    {
        return $user->isAdmin()
            || $user->id === $collocation->owner_id
            || $collocation->members()->where('user_id', $user->id)->whereNull('left_at')->exists();
    }

    /**
     * Only users with the 'owner' role can create a collocation.
     */
    public function create(User $user): bool
    {
        return $user->isOwner() || $user->isAdmin();
    }

    /**
     * Only the specific owner of THIS collocation can update it.
     */
    public function update(User $user, Collocation $collocation): bool
    {
        return $user->id === $collocation->owner_id || $user->isAdmin();
    }

    /**
     * Only the specific owner can cancel their collocation,
     * and only if it hasn't already been cancelled.
     */
    public function cancel(User $user, Collocation $collocation): bool
    {
        return ($user->id === $collocation->owner_id || $user->isAdmin()) && !$collocation->isCancelled();
    }

    /**
     * Owner and members of a collocation can view its member list.
     */
    public function viewMembers(User $user, Collocation $collocation): bool
    {
        return $user->isAdmin()
            || $user->id === $collocation->owner_id
            || $collocation->members()->where('user_id', $user->id)->whereNull('left_at')->exists();
    }

    /**
     * Only the collocation owner can delete it (hard delete, if ever exposed).
     */
    public function delete(User $user, Collocation $collocation): bool
    {
        return $user->id === $collocation->owner_id || $user->isAdmin();
    }
}
