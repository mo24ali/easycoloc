<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

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


    public function isCancelled(): bool
    {
        return $this->cancelled_at !== null;
    }

    public function cancel(): void
    {
        $this->update(['cancelled_at' => now(), 'status' => 'inactive']);
    }





    /**
     * Get detailed expense share breakdown.
     * turns [{user_id, user_name, shares: [{share_id, amount, receiver_id, receiver_name, expense_id, expense_title, payed}]}]
     */
    public function getExpenseShareDetails(): array
    {
        $expenseShares = DB::table('expense_share')
            ->join('expenses', 'expense_share.expense_id', '=', 'expenses.id')
            ->join('users as payer', 'expense_share.payer_id', '=', 'payer.id')
            ->join('users as receiver', 'expenses.member_id', '=', 'receiver.id')
            ->where('expenses.collocation_id', $this->id)
            ->select(
                'expense_share.id as share_id',
                'expense_share.payer_id',
                'payer.name as payer_name',
                'expense_share.share_per_user as amount',
                'expenses.member_id as receiver_id',
                'receiver.name as receiver_name',
                'expenses.id as expense_id',
                'expenses.title as expense_title',
                'expense_share.payed'
            )
            ->orderBy('payer.name')
            ->get();

        // Group by payer
        $result = [];
        foreach ($expenseShares->groupBy('payer_id') as $payerId => $shares) {
            $payerName = $shares->first()->payer_name;
            $result[] = [
                'user_id' => $payerId,
                'user_name' => $payerName,
                'shares' => $shares->map(fn($share) => [
                    'share_id' => $share->share_id,
                    'payer_id' => $share->payer_id,
                    'amount' => (float) $share->amount,
                    'receiver_id' => $share->receiver_id,
                    'receiver_name' => $share->receiver_name,
                    'expense_id' => $share->expense_id,
                    'expense_title' => $share->expense_title,
                    'payed' => (bool) $share->payed,
                ])->toArray(),
            ];
        }

        return $result;
    }

    /**
     * Minimal-transaction reimbursement suggestions based on expense shares
     */


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
