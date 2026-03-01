<?php

namespace App\Services;

use App\Models\Collocation;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use App\Models\ExpenseShare;

/**
 * 
 * service dédié à le calcul de la reputation si balance <= 0  +1 sinon -1
 * 
 */
class CollocationCleanupService
{
    public function __construct(
        private BalanceService $balanceService
    ) {
    }

  
    public function memberLeaves(Collocation $collocation, User $user): array
    {
        return DB::transaction(function () use ($collocation, $user) {
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
                // Solde = 0 → +1 reputation
                $user->increment('reputation_score');
                $reputationChange = '+1';
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
        });
    }

  
    private function createSettlementPayments(Collocation $collocation, User $user, float $balance): void
    {
        if ($balance <= 0)
            return;

       // recuperate the users whom the user owes money to
        $debts = DB::table('expense_share')
            ->join('expenses', 'expense_share.expense_id', '=', 'expenses.id')
            ->where('expense_share.payer_id', $user->id)
            ->where('expenses.collocation_id', $collocation->id)
            ->where('expense_share.payed', false)
            ->selectRaw('expenses.member_id as receiver_id, SUM(expense_share.share_per_user) as total_amount')
            ->groupBy('expenses.member_id')
            ->get();

        // create payments to each
        foreach ($debts as $debt) {
            // prevent ducplication of payments
            $exists = Payment::where('collocation_id', $collocation->id)
                ->where('payer_id', $user->id)
                ->where('receiver_id', $debt->receiver_id)
                ->where('status', 'pending')
                ->exists();

            if (!$exists) {
                Payment::create([
                    'collocation_id' => $collocation->id,
                    'payer_id' => $user->id,
                    'receiver_id' => $debt->receiver_id,
                    'amount' => $debt->total_amount,
                    'status' => 'pending',
                ]);
            }
        }
    }

    /**
     * get a user out of the coloc
     */
    public function ownerRemovesMember(Collocation $collocation, User $memberToRemove): array
    {
        return DB::transaction(function () use ($collocation, $memberToRemove) {
            // compute the balance
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
                
                $details['debt_transferred'] = $memberBalance;
                $details['action'] = 'debt_transferred';
                $details['message'] = "Solde négatif de {$memberToRemove->name} (€{$memberBalance}) "
                    . "transféré au owner {$owner->name}";

                // the owner must pay the debt of the excluded member
                ExpenseShare::where('payer_id', $memberToRemove->id)
                    ->where('payed', false)
                    ->whereHas('expense', fn($q) => $q->where('collocation_id', $collocation->id))
                    ->update(['payer_id' => $owner->id]);

            } elseif ($memberBalance < 0) {
                $details['action'] = 'credit_cancelled';
                $details['message'] = "{$memberToRemove->name} avait €" . abs($memberBalance)
                    . " à recevoir, crédit annulé.";

                
                ExpenseShare::where('payed', false)
                    ->whereHas('expense', fn($q) => $q->where('collocation_id', $collocation->id)->where('member_id', $memberToRemove->id))
                    ->update(['payed' => true]);

            } else {
                $details['action'] = 'no_balance';
                $details['message'] = "{$memberToRemove->name} n'avait pas de dette.";
            }

            // update reputation score
            if ($memberBalance > 0) {
                $memberToRemove->decrement('reputation_score');
                $details['reputation_change'] = '-1';
            } elseif ($memberBalance < 0) {
                $memberToRemove->increment('reputation_score');
                $details['reputation_change'] = '+1';
            } else {
                $memberToRemove->increment('reputation_score');
                $details['reputation_change'] = '+1';
            }

            // mark as gone
            $collocation->members()->updateExistingPivot($memberToRemove->id, [
                'left_at' => now(),
            ]);

            return $details;
        });
    }

}
