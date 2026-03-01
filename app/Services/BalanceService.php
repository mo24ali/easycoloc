<?php

namespace App\Services;

use App\Models\Collocation;


class BalanceService
{
    /**
     * Calculates the total amount paid by a member in a collocation.
     */
    public function getTotalSpentByMember(Collocation $collocation, int $userId): float
    {
        return (float) $collocation->expenses()
            ->where('member_id', $userId)
            ->sum('amount');
    }

    /**
     * Gets the number of active members in a collocation.
     * Includes the owner even if they are not in the members table.
     */
    public function getActiveMemberCount(Collocation $collocation): int
    {
        $activeMembers = $collocation->members()
            ->wherePivotNull('left_at')
            ->count();

        // If the owner is not in the members, add them to the count
        $ownerId = $collocation->owner_id;
        $isOwnerInMembers = $collocation->members()
            ->wherePivotNull('left_at')
            ->where('users.id', $ownerId)
            ->exists();

        if (!$isOwnerInMembers) {
            $activeMembers++;
        }

        return $activeMembers;
    }

    /**
     * Calculates the total expenses of the collocation.
     */
    public function getTotalExpenses(Collocation $collocation): float
    {
        return (float) $collocation->expenses()->sum('amount');
    }

    /**
     * Calculates the individual share for a member.
     */
    public function getIndividualShare(Collocation $collocation): float
    {
        $totalExpenses = $this->getTotalExpenses($collocation);
        $activeMemberCount = $this->getActiveMemberCount($collocation);

        return $activeMemberCount > 0 ? $totalExpenses / $activeMemberCount : 0;
    }

    /**
     * Calculates the NET balance for a member in the collocation.
     */
    public function getMemberBalance(Collocation $collocation, int $userId): float
    {
        $individualShare = $this->getIndividualShare($collocation);
        $totalSpent = $this->getTotalSpentByMember($collocation, $userId);

        return round($individualShare - $totalSpent, 2);
    }
}
