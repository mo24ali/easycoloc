<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Collocation extends Model
{
    use HasFactory;

    protected $fillable = [
        'owner_id',
        'name',
        'status',
        'cancelled_at',
    ];

    protected function casts(): array
    {
        return [
            'cancelled_at' => 'datetime',
        ];
    }

    // ─── Business logic ────────────────────────────────────────────────────────

    public function isCancelled(): bool
    {
        return $this->cancelled_at !== null;
    }

    public function cancel(): void
    {
        $this->update(['cancelled_at' => now(), 'status' => 'inactive']);
    }

    // ─── Balance helpers (Epic 6) ─────────────────────────────────────────────

    /**
     * Sum of all expenses paid by a specific member in this collocation.
     */
    public function getTotalSpentByMember(int $userId): float
    {
        return (float) $this->expenses()
            ->where('member_id', $userId)
            ->sum('amount');
    }

    /**
     * Net balance for a member.
     * Positive = member owes money | Negative = member is owed money
     */
    public function getMemberBalance(int $userId): float
    {
        $totalExpenses = (float) $this->expenses()->sum('amount');
        $memberCount = $this->members()->wherePivotNull('left_at')->count();
        $memberShare = $memberCount > 0 ? $totalExpenses / $memberCount : 0;
        $memberPaid = $this->getTotalSpentByMember($userId);
        return round($memberShare - $memberPaid, 2);
    }

    /**
     * Minimal-transaction reimbursement suggestions.
     * Returns [{payer_id, payer_name, receiver_id, receiver_name, amount}]
     */
    public function getReimbursementSuggestions(): array
    {
        $activeMembers = $this->members()->wherePivotNull('left_at')->get();

        $debtors = collect(); // positive balance → owes
        $creditors = collect(); // negative balance → owed

        foreach ($activeMembers as $member) {
            $balance = $this->getMemberBalance($member->id);
            if ($balance > 0.00) {
                $debtors->put($member->id, ['balance' => $balance, 'name' => $member->name]);
            } elseif ($balance < 0.00) {
                $creditors->put($member->id, ['balance' => abs($balance), 'name' => $member->name]);
            }
        }

        $transactions = [];

        while ($debtors->isNotEmpty() && $creditors->isNotEmpty()) {
            $debtorId = $debtors->keys()->first();
            $creditorId = $creditors->keys()->first();
            $amount = min($debtors[$debtorId]['balance'], $creditors[$creditorId]['balance']);
            $amount = round($amount, 2);

            $transactions[] = [
                'payer_id' => $debtorId,
                'payer_name' => $debtors[$debtorId]['name'],
                'receiver_id' => $creditorId,
                'receiver_name' => $creditors[$creditorId]['name'],
                'amount' => $amount,
            ];

            $debtors[$debtorId]['balance'] = round($debtors[$debtorId]['balance'] - $amount, 2);
            $creditors[$creditorId]['balance'] = round($creditors[$creditorId]['balance'] - $amount, 2);

            if ($debtors[$debtorId]['balance'] <= 0)
                $debtors->forget($debtorId);
            if ($creditors[$creditorId]['balance'] <= 0)
                $creditors->forget($creditorId);
        }

        return $transactions;
    }

    // ─── Scopes ──────────────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->whereNull('cancelled_at');
    }

    // ─── Relationships ────────────────────────────────────────────────────────

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'collocation_user')
            ->withPivot('role', 'balance', 'joined_at', 'left_at')
            ->withTimestamps();
    }

    public function invitations(): HasMany
    {
        return $this->hasMany(Invitation::class);
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }
}
