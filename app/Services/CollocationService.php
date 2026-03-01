<?php

namespace App\Services;

use App\Models\Collocation;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Exception;

class CollocationService
{
    /**
     * Pass ownership logic correctly transferring pivot roles.
     */
    public function passOwnership(Collocation $collocation, User $newOwner): void
    {
        if ($collocation->owner_id === $newOwner->id) {
            throw new Exception('This member is already the owner.');
        }

        $isMember = $collocation->members()->where('users.id', $newOwner->id)->wherePivotNull('left_at')->exists();
        if (!$isMember) {
            throw new Exception('The user must be an active member of this collocation to receive ownership.');
        }

        DB::transaction(function () use ($collocation, $newOwner) {
            $oldOwnerId = $collocation->owner_id;
            $oldOwner = User::find($oldOwnerId);

            //  Update Collocation owner
            $collocation->update(['owner_id' => $newOwner->id]);

            //  Update new owner pivot role
            $collocation->members()->updateExistingPivot($newOwner->id, [
                'role' => 'owner'
            ]);

            // Update old owner pivot role
            if ($oldOwner) {
                $collocation->members()->updateExistingPivot($oldOwnerId, [
                    'role' => 'member'
                ]);

                // Update old owner User model role if they don't own any other collocations
                if (!$oldOwner->ownedCollocations()->exists() && !$oldOwner->isAdmin()) {
                    $oldOwner->update(['role' => 'member']);
                }
            }

            // Update new owner User model role
            if (!$newOwner->isAdmin()) {
                $newOwner->update(['role' => 'owner']);
            }
        });
    }

    /**
     * Complete Member Leave checks updating explicit models after cleanup rules.
     */
    public function completeMemberLeave(User $user): void
    {
        // If the user has no remaining active collocations, reset their role to 'user'
        // so they can join or create a new collocation
        $hasOtherCollocations = $user->collocations()
            ->wherePivotNull('left_at')
            ->where('collocations.status', 'active')
            ->exists();

        if (!$hasOtherCollocations && !$user->isAdmin()) {
            $user->update(['role' => 'user']);
        }
    }
}
