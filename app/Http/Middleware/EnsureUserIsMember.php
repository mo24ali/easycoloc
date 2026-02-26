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

        if (!$user || (!$user->isMember() && !$user->isOwner())) {
            abort(403, 'This action requires Member privileges.');
        }

        return $next($request);
    }
}
