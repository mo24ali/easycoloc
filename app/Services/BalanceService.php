<?php

namespace App\Services;

use App\Models\Collocation;


class BalanceService
{
    /**
     * Calcule le total payé par un membre dans une colocation.
     */
    public function getTotalSpentByMember(Collocation $collocation, int $userId): float
    {
        return (float) $collocation->expenses()
            ->where('member_id', $userId)
            ->sum('amount');
    }

    /**
     * Obtient le nombre de membres actifs dans une colocation.
     * Inclut le propri\u00e9taire m\u00eame s'il n'est pas dans la table members.
     * */
    public function getActiveMemberCount(Collocation $collocation): int
    {
        $activeMembers = $collocation->members()
            ->wherePivotNull('left_at')
            ->count();

        // Si le propri\u00e9taire n'est pas dans les membres, l'ajouter au compte
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
     * Calcule le total de toutes les dépenses de la colocation.
     */
    public function getTotalExpenses(Collocation $collocation): float
    {
        return (float) $collocation->expenses()->sum('amount');
    }

    /**
     * Calcule la part individuelle d'un membre.
     */
    public function getIndividualShare(Collocation $collocation): float
    {
        $totalExpenses = $this->getTotalExpenses($collocation);
        $activeMemberCount = $this->getActiveMemberCount($collocation);

        return $activeMemberCount > 0 ? $totalExpenses / $activeMemberCount : 0;
    }

    /**
     * Calcule le solde NET d'un membre dans la colocation.
     */
    public function getMemberBalance(Collocation $collocation, int $userId): float
    {
        $individualShare = $this->getIndividualShare($collocation);
        $totalSpent = $this->getTotalSpentByMember($collocation, $userId);

        return round($individualShare - $totalSpent, 2);
    }

    /**
     * Obtient les détails complets des shares pour tous les membres.
     */
    public function getDetailedSharesWithBalance(Collocation $collocation): array
    {
        $shareDetails = $collocation->getExpenseShareDetails();
        $result = [];

        foreach ($shareDetails as $userShare) {
            $userId = $userShare['user_id'];
            $balance = $this->getMemberBalance($collocation, $userId);

            $result[] = [
                'user_id' => $userId,
                'user_name' => $userShare['user_name'],
                'balance' => $balance,
                'shares' => $userShare['shares'],
            ];
        }

        return $result;
    }

    /**
     * Obtient les statistiques globales de la colocation.
     */
    public function getStatistics(Collocation $collocation): array
    {
        $totalExpenses = $this->getTotalExpenses($collocation);
        $activeMemberCount = $this->getActiveMemberCount($collocation);
        $individualShare = $this->getIndividualShare($collocation);

        return [
            'total_expenses' => $totalExpenses,
            'active_member_count' => $activeMemberCount,
            'individual_share' => $individualShare,
            'average_per_member' => $activeMemberCount > 0 ? $totalExpenses / $activeMemberCount : 0,
        ];
    }
}
