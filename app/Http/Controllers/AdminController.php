<?php

namespace App\Http\Controllers;

use App\Models\Collocation;
use App\Models\Expense;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminController extends Controller
{
    /**
     * Show the admin dashboard with stats and user management.
     */
    public function dashboard(): View
    {
        // Get stats
        $totalUsers = User::count();
        $totalAdmins = User::where('role', 'admin')->count();
        $totalBannedUsers = User::where('is_banned', true)->count();
        $totalCollocations = Collocation::count();
        $totalExpenses = Expense::sum('amount') ?? 0;
        $totalMembers = User::whereIn('role', ['member', 'owner'])->count();

        // Get all users with pagination
        $users = User::orderByDesc('created_at')->paginate(15);

        return view('admin.dashboard', compact(
            'totalUsers',
            'totalAdmins',
            'totalBannedUsers',
            'totalCollocations',
            'totalExpenses',
            'totalMembers',
            'users'
        ));
    }

    /**
     * Ban a user.
     */
    public function ban(User $user): RedirectResponse
    {
        if ($user->isAdmin()) {
            return back()->withErrors(['user' => 'Cannot ban an admin user.']);
        }

        $user->update(['is_banned' => true]);

        return back()->with('status', "{$user->name} has been banned.");
    }

    /**
     * Unban a user.
     */
    public function unban(User $user): RedirectResponse
    {
        $user->update(['is_banned' => false]);

        return back()->with('status', "{$user->name} has been unbanned.");
    }

    /**
     * Delete a user and their data.
     */
    public function destroy(User $user): RedirectResponse
    {
        if ($user->isAdmin()) {
            return back()->withErrors(['user' => 'Cannot delete an admin user.']);
        }

        $name = $user->name;
        $user->delete();

        return back()->with('status', "{$name} has been deleted.");
    }
}
