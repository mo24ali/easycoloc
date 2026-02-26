<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckIfBanned
{
    /**
     * Handle an incoming request.
     * Logs out and redirects banned users with an error message.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()?->is_banned) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect('/')
                ->withErrors(['email' => 'Your account has been suspended. Please contact support.']);
        }

        return $next($request);
    }
}
