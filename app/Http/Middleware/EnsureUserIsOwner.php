<?php

namespace App\Http\Middleware;

use App\Models\Collocation;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsOwner
{
    /**
     * Handle an incoming request.
     * Allows access if user has 'owner' role OR owns the specific collocation.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            abort(403, 'This action requires Owner privileges.');
        }

        if ($user->isAdmin()) {
            return $next($request);
        }

        $collocation = $request->route('collocation');

        if ($collocation instanceof Collocation) {
            if ($collocation->owner_id === $user->id) {
                return $next($request);
            }
            abort(403, 'You must be the owner of this collocation.');
        } else {
            // General check: must be owner of at least one collocation
            if ($user->isOwner()) {
                return $next($request);
            }
        }

        abort(403, 'This action requires Owner privileges.');
    }
}
