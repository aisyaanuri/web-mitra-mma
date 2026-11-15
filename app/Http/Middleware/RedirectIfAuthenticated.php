<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$guards): Response
    {
        $guards = empty($guards) ? [null] : $guards;

        // If an authenticated user explicitly submits a login attempt (POST request),
        // we must NOT blindly redirect them to the dashboard.
        // Doing so would allow anyone who navigated back from an active session to
        // submit an invalid password and still be admitted to the dashboard!
        if ($request->isMethod('POST')) {
            foreach ($guards as $guard) {
                if (Auth::guard($guard)->check()) {
                    Auth::guard($guard)->logout();
                }
            }

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return $next($request);
        }

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                return redirect()->to(config('fortify.home', '/dashboard'));
            }
        }

        return $next($request);
    }
}
