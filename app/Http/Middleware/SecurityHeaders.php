<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Response hardening applied to every request.
 *
 * A Content Security Policy is deliberately not set here: Livewire and Filament
 * both need script and style allowances that vary by build, and a policy that is
 * wrong in production is worse than one applied at the web server after the
 * exact asset set is known. The deployment guide covers it.
 */
class SecurityHeaders
{
    /**
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->add([
            'X-Content-Type-Options' => 'nosniff',
            'X-Frame-Options' => 'SAMEORIGIN',
            'Referrer-Policy' => 'strict-origin-when-cross-origin',
            // The site needs none of these; denying them shrinks the attack surface
            // of any third-party embed, including the contact page map.
            'Permissions-Policy' => 'camera=(), microphone=(), geolocation=(), payment=(), usb=()',
            'Cross-Origin-Opener-Policy' => 'same-origin',
        ]);

        // Donation pages carry donor detail and must never be cached by a shared
        // proxy or indexed.
        if ($request->is('donate/receipt/*', 'donate/thank-you/*')) {
            $response->headers->set('Cache-Control', 'private, no-store, max-age=0');
            $response->headers->set('X-Robots-Tag', 'noindex, nofollow');
        }

        if ($request->is('admin', 'admin/*', 'search', 'newsletter/*', 'donate/callback') || $response->getStatusCode() >= 400) {
            $response->headers->set('X-Robots-Tag', 'noindex, follow');
        }

        if ($request->secure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        return $response;
    }
}
