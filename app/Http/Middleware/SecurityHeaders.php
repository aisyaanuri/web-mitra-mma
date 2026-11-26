<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Baseline security headers (SR-25). The CSP intentionally allows
 * 'unsafe-inline'/'unsafe-eval' for scripts and 'unsafe-inline' for
 * styles — Alpine.js (bundled with Livewire) evaluates x-data/x-init
 * expressions via the Function constructor and Flux/Livewire inject
 * inline styles at runtime, so a strict nonce-based CSP would break
 * the admin panel and public site alike without a larger rework. The
 * policy still blocks the main threat this header defends against:
 * a script/style/frame injected from an arbitrary third-party origin.
 */
class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $scriptSrc = "'self' 'unsafe-inline' 'unsafe-eval'";
        $styleSrc = "'self' 'unsafe-inline'";
        $fontSrc = "'self' data:";
        $connectSrc = "'self'";

        if (app()->environment('local')) {
            $vite = 'http://localhost:5173 http://127.0.0.1:5173 http://[::1]:5173';
            $viteWs = 'ws://localhost:5173 ws://127.0.0.1:5173 ws://[::1]:5173';

            $scriptSrc .= " {$vite}";
            $styleSrc .= " {$vite}";
            $fontSrc .= " {$vite}";
            $connectSrc .= " {$vite} {$viteWs}";
        }

        $response->headers->set('Content-Security-Policy', implode('; ', [
            "default-src 'self'",
            "script-src {$scriptSrc}",
            "style-src {$styleSrc}",
            "img-src 'self' data:",
            "font-src {$fontSrc}",
            'frame-src https://www.google.com',
            "frame-ancestors 'none'",
            "object-src 'none'",
            "base-uri 'self'",
            "form-action 'self'",
            "connect-src {$connectSrc}",
        ]));

        if ($request->secure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        if ($request->user() || $request->is('login*', 'logout*', 'two-factor*', 'passkeys*', 'admin*', 'dashboard*', 'settings*')) {
            $response->headers->set('Cache-Control', 'no-cache, no-store, max-age=0, must-revalidate');
            $response->headers->set('Pragma', 'no-cache');
            $response->headers->set('Expires', 'Fri, 01 Jan 1990 00:00:00 GMT');
        }

        return $response;
    }
}
