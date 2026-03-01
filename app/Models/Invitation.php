<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Invitation extends Model
{
    /** @use HasFactory<\Database\Factories\InvitationFactory> */
    use HasFactory;

    protected $fillable = [
        'collocation_id',
        'sender_id',
        'email',
        'token',
        'status',
        'expires_at',
        'accepted_at',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'accepted_at' => 'datetime',
        ];
    }


    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isValid(): bool
    {
        return $this->isPending() && !$this->isExpired();
    }

   
    public function accept(int $userId): void
    {
        $this->update(['status' => 'accepted', 'accepted_at' => now()]);

        $collocation = $this->collocation;
        if (!$collocation->members()->where('user_id', $userId)->exists()) {
            $collocation->members()->attach($userId, ['joined_at' => now()]);
        }

        // Promote user role to 'member' if still plain 'user'
        $user = User::find($userId);
        if ($user && $user->isNormalUser()) {
            $user->update(['role' => 'member']);
        }
    }

    public function collocation(): BelongsTo
    {
        return $this->belongsTo(Collocation::class);
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }


    public static function generateToken(): string
    {
        do {
            $token = Str::random(64);
        } while (static::where('token', $token)->exists());

        return $token;
    }
}
