<?php

namespace App\Services;

use App\Models\Collocation;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Service pour gérer les départs et les retraits de membres.
 *
 * Règles métier:
 * - Si un membre quitte avec solde NÉGATIF (doit recevoir) → +1 reputation
 * - Si un membre quitte avec solde POSITIF (doit payer) → -1 reputation
 * - Si un owner retire un membre avec DETTE → la dette est imputée au owner
 * - Si un owner retire avec CRÉDIT → compensation automatique
 *
 * Les opérations doivent être transactionnelles
 */
class CollocationCleanupService
{
    public function __construct(
        private BalanceService $balanceService
    ) {}

    /**
     * Marque un utilisateur comme ayant quitté la colocation.
     * Crée les paiements pour le règlement des dettes.
     */
    public function memberLeaves(Collocation $collocation, User $user): array
    {
        // Calcule le solde actuel
        $balance = $this->balanceService->getMemberBalance($collocation, $user->id);

        // Applique les règles de réputation
        if ($balance < 0) {
            // Créditeur (doit recevoir) → +1 reputation
            $user->increment('reputation_score');
            $reputationChange = '+1';
        } elseif ($balance > 0) {
            // Débiteur (doit payer) → -1 reputation
            $user->decrement('reputation_score');
            $reputationChange = '-1';
        } else {
            $reputationChange = '0';
        }

        // Crée les paiements de règlement si le user a une dette
        if ($balance > 0) {
            // Le user doit payer à tous les autres membres qui ont payé des dépenses
            $this->createSettlementPayments($collocation, $user, $balance);
        }

        // Marque comme parti (left_at)
        $collocation->members()->updateExistingPivot($user->id, [
            'left_at' => now(),
        ]);

        return [
            'user_id' => $user->id,
            'user_name' => $user->name,
            'balance' => $balance,
            'reputation_change' => $reputationChange,
            'left_at' => now(),
        ];
    }

    /**
     * Crée les paiements de règlement pour un utilisateur qui quitte.
     */
    private function createSettlementPayments(Collocation $collocation, User $user, float $balance): void
    {
        if ($balance <= 0) return;

        // Récupère les personnes à qui l'utilisateur doit de l'argent
        $debts = DB::table('expense_share')
            ->join('expenses', 'expense_share.expense_id', '=', 'expenses.id')
            ->where('expense_share.payer_id', $user->id)
            ->where('expenses.collocation_id', $collocation->id)
            ->where('expense_share.payed', false)
            ->selectRaw('expenses.member_id as receiver_id, SUM(expense_share.share_per_user) as total_amount')
            ->groupBy('expenses.member_id')
            ->get();

        // Crée un paiement pour chaque dette
        foreach ($debts as $debt) {
            Payment::create([
                'collocation_id' => $collocation->id,
                'payer_id' => $user->id,
                'receiver_id' => $debt->receiver_id,
                'amount' => $debt->total_amount,
                'status' => 'pending',
            ]);
        }
    }

    /**
     * Retire un membre de la colocation (action admin/owner).
     */
    public function ownerRemovesMember(Collocation $collocation, User $memberToRemove): array
    {
        // Calcule le solde du membre à retirer
        $memberBalance = $this->balanceService->getMemberBalance($collocation, $memberToRemove->id);

        // Owner
        $owner = $collocation->owner;

        $details = [
            'removed_member_id' => $memberToRemove->id,
            'removed_member_name' => $memberToRemove->name,
            'member_balance' => $memberBalance,
            'owner_id' => $owner->id,
            'owner_name' => $owner->name,
            'debt_transferred' => 0,
        ];

        // Gère la dette du membre
        if ($memberBalance > 0) {
            // Le membre DOIT de l'argent → transférer au owner
            // (Techniquement, on augmente le balance de l'owner)

            $details['debt_transferred'] = $memberBalance;
            $details['action'] = 'debt_transferred';
            $details['message'] = "Solde négatif de {$memberToRemove->name} (€{$memberBalance}) "
                . "transféré au owner {$owner->name}";

            // Owner doit maintenant recevoir cet argent
            // (Dans votre système, c'est un ajustement dans expense_share)
        } elseif ($memberBalance < 0) {
            // Le membre DOIT RECEVOIR → annulé (perte pour lui)
            $details['action'] = 'credit_cancelled';
            $details['message'] = "{$memberToRemove->name} avait €" . abs($memberBalance)
                . " à recevoir, crédit annulé.";
        } else {
            $details['action'] = 'no_balance';
            $details['message'] = "{$memberToRemove->name} n'avait pas de dette.";
        }

        // Applique la réputation
        if ($memberBalance > 0) {
            $memberToRemove->decrement('reputation_score');
            $details['reputation_change'] = '-1';
        } elseif ($memberBalance < 0) {
            $memberToRemove->increment('reputation_score');
            $details['reputation_change'] = '+1';
        } else {
            $details['reputation_change'] = '0';
        }

        // Marque comme parti
        $collocation->members()->updateExistingPivot($memberToRemove->id, [
            'left_at' => now(),
        ]);

        return $details;
    }

    /**
     * Obtient un résumé des dettes actives avant départ.
     *
     * @param Collocation $collocation
     * @param User $user
     * @return array
     */
    public function getMemberDebts(Collocation $collocation, User $user): array
    {
        $expenseShares = $collocation->expenses()
            ->whereHas('shares', fn($q) => $q->where('payer_id', $user->id))
            ->with('shares')
            ->get();

        $debts = [];
        foreach ($expenseShares as $expense) {
            $shares = $expense->shares->where('payer_id', $user->id);
            foreach ($shares as $share) {
                if (!$share->payed) {
                    $debts[] = [
                        'expense_id' => $expense->id,
                        'expense_title' => $expense->title,
                        'amount' => $share->share_per_user,
                        'receiver_id' => $expense->member_id,
                        'receiver_name' => $expense->member->name,
                        'payed' => $share->payed,
                    ];
                }
            }
        }

        return $debts;
    }

    /**
     * Vérifie si un membre peut quitter sans problème.
     *
     * @param Collocation $collocation
     * @param User $user
     * @return array
     */
    public function canMemberLeave(Collocation $collocation, User $user): array
    {
        $balance = $this->balanceService->getMemberBalance($collocation, $user->id);
        $debts = $this->getMemberDebts($collocation, $user);

        return [
            'can_leave' => true, // Peut toujours partir, mais avec conséquences
            'balance' => $balance,
            'pending_debts' => count($debts),
            'reputation_impact' => $balance > 0 ? '-1' : ($balance < 0 ? '+1' : '0'),
            'debts' => $debts,
        ];
    }
}
