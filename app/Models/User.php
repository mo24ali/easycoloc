<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'reputation_score',
        'is_banned',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_banned' => 'boolean',
        ];
    }

    // ─── Role helpers ─────────────────────────────────────────────────────────

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isOwner(): bool
    {
        return $this->role === 'owner';
    }

    public function isMember(): bool
    {
        return $this->role === 'member';
    }

    public function isUser(): bool
    {
        return $this->role === 'user';
    }

    // ─── Relationships ────────────────────────────────────────────────────────

    /**
     * Collocations this user owns (as 'owner').
     */
    public function ownedCollocations(): HasMany
    {
        return $this->hasMany(Collocation::class, 'owner_id');
    }

    /**
     * Collocations this user belongs to as a member (many-to-many).
     */
    public function collocations(): BelongsToMany
    {
        return $this->belongsToMany(Collocation::class, 'collocation_user')
            ->withPivot('role', 'balance', 'joined_at', 'left_at')
            ->withTimestamps();
    }

    /**
     * Invitations sent by this user.
     */
    public function sentInvitations(): HasMany
    {
        return $this->hasMany(Invitation::class, 'sender_id');
    }

    /**
     * Expenses added by this user.
     */
    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class, 'member_id');
    }

    /**
     * Payments this user made (as payer).
     */
    public function paymentsAsPayer(): HasMany
    {
        return $this->hasMany(Payment::class, 'payer_id');
    }

    /**
     * Payments this user is set to receive.
     */
    public function paymentsAsReceiver(): HasMany
    {
        return $this->hasMany(Payment::class, 'receiver_id');
    }
}
