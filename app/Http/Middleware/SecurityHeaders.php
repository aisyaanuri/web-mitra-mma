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
        $response->headers->set('Content-Security-Policy', implode('; ', [
            "default-src 'self'",
            "script-src 'self' 'unsafe-inline' 'unsafe-eval'",
            "style-src 'self' 'unsafe-inline'",
            "img-src 'self' data:",
            "font-src 'self' data:",
            'frame-src https://www.google.com',
            "frame-ancestors 'none'",
            "object-src 'none'",
            "base-uri 'self'",
            "form-action 'self'",
            "connect-src 'self'",
        ]));

        if ($request->secure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        return $response;
    }
}
