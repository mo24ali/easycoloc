<?php

namespace App\Services;

use App\Models\Collocation;
use Illuminate\Support\Collection;

/**
 * Service for optimizing settlement transactions.
 * peer-to-peer 
 * get the high debtors and high creditors and match them to optimize transactions
 * so we don't make too many transactions
 */
class DebtOptimizationService
{
    /**
     * Calculates optimized transactions for a collocation.
     */
    public function getOptimizedTransactions(Collocation $collocation, BalanceService $balanceService): array
    {
        // Retrieve all active members with their balances
        $memberBalances = $this->getMemberBalances($collocation, $balanceService);

        // Separate creditors and debtors
        $creditors = $this->filterCreditors($memberBalances);
        $debtors = $this->filterDebtors($memberBalances);

        // Optimize and return transactions
        return $this->optimizeTransactions($creditors, $debtors);
    }

    /**
     * Retrieve balances for all active members.
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
        })->filter(fn($member) => $member['balance'] != 0); 
    }

    /**
     * Filter creditors (members who need to pay).
     */
    private function filterCreditors(Collection $memberBalances): Collection
    {
        return $memberBalances
            ->filter(fn($member) => $member['balance'] > 0)
            ->values();
    }

    /**
     * Filter debtors (members who need to receive).
     */
    private function filterDebtors(Collection $memberBalances): Collection
    {
        return $memberBalances
            ->filter(fn($member) => $member['balance'] < 0)
            ->map(fn($member) => [...$member, 'balance' => abs($member['balance'])])
            ->values();
    }

    /**
     * Transaction optimization algorithm.
     */
    private function optimizeTransactions(Collection $creditors, Collection $debtors): array
    {
        $transactions = [];
        $creditorsList = $creditors->toArray();
        $debtorsList = $debtors->toArray();

        while (!empty($creditorsList) && !empty($debtorsList)) {
            // Take the first creditor and debtor
            $creditor = &$creditorsList[0];
            $debtor = &$debtorsList[0];

            // Calculate the amount to transfer (the minimum of the two)
            $amount = min($creditor['balance'], $debtor['balance']);
            $amount = round($amount, 2);

            // Record the transaction
            $transactions[] = [
                'payer_id' => $creditor['id'],
                'payer_name' => $creditor['name'],
                'receiver_id' => $debtor['id'],
                'receiver_name' => $debtor['name'],
                'amount' => $amount,
            ];

            // Reduce balances
            $creditorsList[0]['balance'] = round($creditorsList[0]['balance'] - $amount, 2);
            $debtorsList[0]['balance'] = round($debtorsList[0]['balance'] - $amount, 2);

            // Remove those whose balance is zero
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
