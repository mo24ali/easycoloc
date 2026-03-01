<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use App\Services\AdminService;

class AdminController extends Controller
{
    public function __construct(
        private AdminService $adminService
    ) {
    }

    /**
     * Show the admin dashboard with stats and user management
     */
    public function dashboard(): View
    {
        // Get stats
        $stats = $this->adminService->getDashboardStats();

        // Get all users with pagination
        $users = User::orderByDesc('created_at')->paginate(15);

        return view('admin.dashboard', array_merge($stats, ['users' => $users]));
    }

    /**
     * Ban a user
     */
    public function ban(User $user): RedirectResponse
    {
            if (!$this->adminService->banUser($user)) {
                return back()->withErrors(['user' => 'Cannot ban an admin user.']);
            }

        return back()->with('status', "{$user->name} has been banned.");
    }

    /**
     * Unban a user.
     */
    public function unban(User $user): RedirectResponse
    {
        $this->adminService->unbanUser($user);

        return back()->with('status', "{$user->name} has been unbanned.");
    }

    /**
     * Delete a user and their data.
     */
    public function destroy(User $user): RedirectResponse
    {
        $name = $user->name;

        if (!$this->adminService->deleteUser($user)) {
            return back()->withErrors(['user' => 'Cannot delete an admin user.']);
        }

        return back()->with('status', "{$name} has been deleted.");
    }
}
