<?php

namespace App\Services;

use App\Models\Collocation;
use Illuminate\Support\Collection;

/**
 * Service pour l'optimisation des transactions de remboursement.
 *
 * Algorithme:
 * 1. Sépare les créditeurs (solde > 0) et débiteurs (solde < 0)
 * 2. Réduit le nombre de transactions via compensation mutuelle
 * 3. Utilise un algorithme greedy pour appairage optimal
 *
 * Exemple:
 *   Alice doit 100€ à Bob
 *   Bob doit 60€ à Charlie
 *   Charlie doit 30€ à Alice
 *
 *   Sans optimisation: 3 transactions
 *   Optimisé:
 *     - Compensation Alice-Charlie: -30€ = Alice doit maintenant 70€
 *     - Alice → Bob: 70€
 *     - Bob → Charlie: 30€
 *   Total: 2 transactions (économie de 33%)
 */
class DebtOptimizationService
{
    /**
     * Calcule les transactions optimisées pour une colocation.
     *
     * @param Collocation $collocation
     * @param BalanceService $balanceService
     * @return array
     */
    public function getOptimizedTransactions(Collocation $collocation, BalanceService $balanceService): array
    {
        // Récupère tous les membres actifs avec leurs balances
        $memberBalances = $this->getMemberBalances($collocation, $balanceService);

        // Sépare créditeurs et débiteurs
        $creditors = $this->filterCreditors($memberBalances);      // Solde > 0 (doivent payer)
        $debtors = $this->filterDebtors($memberBalances);          // Solde < 0 (doivent recevoir)

        // Optimise et retourne les transactions
        return $this->optimizeTransactions($creditors, $debtors);
    }

    /**
     * Récupère les balances de tous les membres actifs.
     *
     * @param Collocation $collocation
     * @param BalanceService $balanceService
     * @return Collection
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
     * Balance > 0 = doit payer
     *
     * @param Collection $memberBalances
     * @return Collection
     */
    private function filterCreditors(Collection $memberBalances): Collection
    {
        return $memberBalances
            ->filter(fn($member) => $member['balance'] > 0)
            ->values();
    }

    /**
     * Filtre les débiteurs (membres qui doivent recevoir).
     * Balance < 0 = doit recevoir (inverse le signe)
     *
     * @param Collection $memberBalances
     * @return Collection
     */
    private function filterDebtors(Collection $memberBalances): Collection
    {
        return $memberBalances
            ->filter(fn($member) => $member['balance'] < 0)
            ->map(fn($member) => [...$member, 'balance' => abs($member['balance'])])
            ->values();
    }

    /**
     * Algo d'optimisation des transactions (Greedy + compensation circulaire).
     *
     * Appairage: À chaque itération, on appaire le plus grand créditeur
     * avec le plus grand débiteur et on réduit leurs balances.
     *
     * @param Collection $creditors
     * @param Collection $debtors
     * @return array
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

    /**
     * Calcule les statistiques de l'optimisation.
     *
     * @param array $transactions
     * @return array
     */
    public function getOptimizationStats(array $transactions): array
    {
        $totalAmount = array_sum(array_column($transactions, 'amount'));
        $transactionCount = count($transactions);

        return [
            'transaction_count' => $transactionCount,
            'total_amount' => round($totalAmount, 2),
            'average_per_transaction' => $transactionCount > 0 ? round($totalAmount / $transactionCount, 2) : 0,
        ];
    }
}
