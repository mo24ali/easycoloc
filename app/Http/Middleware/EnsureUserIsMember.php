<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsMember
{
    /**
     * Handle an incoming request.
     * Allows access to users with the 'member' OR 'owner' role.
     * Owners can always access member-level resources.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            abort(403, 'This action requires Member privileges.');
        }

        if ($user->isAdmin()) {
            return $next($request);
        }

        $collocation = $request->route('collocation');

        if ($collocation instanceof \App\Models\Collocation) {
            // Contextual check
            $isMember = $collocation->members()->where('user_id', $user->id)->wherePivotNull('left_at')->exists();
            $isOwner = $collocation->owner_id === $user->id;

            if (!$isMember && !$isOwner) {
                abort(403, 'You must be a member or owner of this collocation.');
            }
        } else {
            // General check: must be a member or owner of AT LEAST ONE collocation
            if (!$user->isMember() && !$user->isOwner()) {
                abort(403, 'This action requires Member privileges.');
            }
        }

        return $next($request);
    }
}
