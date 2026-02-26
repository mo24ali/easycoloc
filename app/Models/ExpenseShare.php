<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExpenseShare extends Model
{
    use HasFactory;

    protected $table = 'expense_share';

    protected $fillable = [
        'payer_id',
        'share_per_user',
        'payed',
        'expense_id',
    ];

    protected function casts(): array
    {
        return [
            'share_per_user' => 'decimal:2',
            'payed' => 'boolean',
        ];
    }

    // ─── Relationships ────────────────────────────────────────────────────────

    public function payer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'payer_id');
    }

    public function expense(): BelongsTo
    {
        return $this->belongsTo(Expense::class);
    }
}
