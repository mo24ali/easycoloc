<?php

namespace App\Services;

use App\Models\Collocation;
use Illuminate\Support\Collection;

/**
 * Service pour l'optimisation des transactions de remboursement.
 * peer- to-peer
 * get the high debitors and high creeditors and match them to optimize transactions
 * so we dont makt tooo much transactions
 */
class DebtOptimizationService
{
    /**
     * Calcule les transactions optimisées pour une colocation.
     */
    public function getOptimizedTransactions(Collocation $collocation, BalanceService $balanceService): array
    {
        // Récupère tous les membres actifs avec leurs balances
        $memberBalances = $this->getMemberBalances($collocation, $balanceService);

        // Sépare créditeurs et débiteurs
        $creditors = $this->filterCreditors($memberBalances);
        $debtors = $this->filterDebtors($memberBalances);

        // Optimise et retourne les transactions
        return $this->optimizeTransactions($creditors, $debtors);
    }

    /**
     * Récupère les balances de tous les membres actifs.
     */
    private function getMemberBalances(Collocation $collocation, BalanceService $balanceService): Collection
    {
        $activeMembers = $collocation->members()
            ->wherePivotNull('left_at')
            ->get();

        return $activeMembers->map(function ($member) use ($collocation, $balanceService) {
            return [
                'id' => $member->id,
                'name' => $member->name,
                'balance' => $balanceService->getMemberBalance($collocation, $member->id),
            ];
        })->filter(fn($member) => $member['balance'] != 0); // Exclut les soldes = 0
    }

    /**
     * Filtre les créditeurs (membres qui doivent payer).
     */
    private function filterCreditors(Collection $memberBalances): Collection
    {
        return $memberBalances
            ->filter(fn($member) => $member['balance'] > 0)
            ->values();
    }

    /**
     * Filtre les débiteurs (membres qui doivent recevoir).
     */
    private function filterDebtors(Collection $memberBalances): Collection
    {
        return $memberBalances
            ->filter(fn($member) => $member['balance'] < 0)
            ->map(fn($member) => [...$member, 'balance' => abs($member['balance'])])
            ->values();
    }

    /**
     * Algo d'optimisation des transactions.
     */
    private function optimizeTransactions(Collection $creditors, Collection $debtors): array
    {
        $transactions = [];
        $creditorsList = $creditors->toArray();
        $debtorsList = $debtors->toArray();

        while (!empty($creditorsList) && !empty($debtorsList)) {
            // Prend le premier créditeur et débiteur
            $creditor = &$creditorsList[0];
            $debtor = &$debtorsList[0];

            // Calcule le montant à transférer (le min des deux)
            $amount = min($creditor['balance'], $debtor['balance']);
            $amount = round($amount, 2);

            // Enregistre la transaction
            $transactions[] = [
                'payer_id' => $creditor['id'],
                'payer_name' => $creditor['name'],
                'receiver_id' => $debtor['id'],
                'receiver_name' => $debtor['name'],
                'amount' => $amount,
            ];

            // Réduit les balances
            $creditorsList[0]['balance'] = round($creditorsList[0]['balance'] - $amount, 2);
            $debtorsList[0]['balance'] = round($debtorsList[0]['balance'] - $amount, 2);

            // Retire ceux dont la balance est = 0
            if ($creditorsList[0]['balance'] <= 0.01) {
                array_shift($creditorsList);
            }
            if ($debtorsList[0]['balance'] <= 0.01) {
                array_shift($debtorsList);
            }
        }

        return $transactions;
    }

}
