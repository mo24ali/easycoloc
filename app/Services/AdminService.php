<?php

namespace App\Services;

use App\Models\Collocation;
use App\Models\Expense;
use App\Models\User;

class AdminService
{
    /**
     * Get dashboard statistics cleanly encapsulated.
     */
    public function getDashboardStats(): array
    {
        return [
            'totalUsers' => User::count(),
            'totalAdmins' => User::where('role', 'admin')->count(),
            'totalBannedUsers' => User::where('is_banned', true)->count(),
            'totalCollocations' => Collocation::count(),
            'totalExpenses' => Expense::sum('amount') ?? 0,
            'totalMembers' => User::whereIn('role', ['member', 'owner'])->count(),
        ];
    }

    /**
     * Ban a user safely.
     */
    public function banUser(User $user): bool
    {
        if ($user->isAdmin()) {
            return false;
        }

        return $user->update(['is_banned' => true]);
    }

    /**
     * Unban a user.
     */
    public function unbanUser(User $user): bool
    {
        return $user->update(['is_banned' => false]);
    }

    /**
     * Delete a generic user.
     */
    public function deleteUser(User $user): bool
    {
        if ($user->isAdmin()) {
            return false;
        }

        return $user->delete();
    }
}
