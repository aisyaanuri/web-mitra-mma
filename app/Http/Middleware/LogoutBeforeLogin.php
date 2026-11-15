<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LogoutBeforeLogin
{
    /**
     * Log out the current Fortify guard before guest middleware handles login.
     *
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->routeIs('login.store') || ! $request->isMethod('post')) {
            return $next($request);
        }

        $guard = auth()->guard(config('fortify.guard'));

        if ($guard->check()) {
            $guard->logout();
        }

        return $next($request);
    }
}
