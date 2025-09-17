<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SecureHeadersMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // Only process HTML responses
        if (method_exists($response, 'header')) {
            // Security Headers
            $response->headers->set('X-Content-Type-Options', 'nosniff');
            $response->headers->set('X-XSS-Protection', '1; mode=block');
            $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
            $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
            
            // Content Security Policy
            $csp = [
                "default-src 'self';",
                "script-src 'self' 'unsafe-inline' 'unsafe-eval' https:;",
                "style-src 'self' 'unsafe-inline' https:;",
                "img-src 'self' data: https:;",
                "font-src 'self' data: https:;",
                "connect-src 'self' https:;",
                "frame-ancestors 'self';",
                "form-action 'self';",
                "upgrade-insecure-requests;",
            ];
            
            $response->headers->set('Content-Security-Policy', implode(' ', $csp));
            
            // Feature Policy
            $featurePolicy = [
                "geolocation 'self';",
                "microphone 'none';",
                "camera 'none';",
                "payment 'none';",
            ];
            $response->headers->set('Permissions-Policy', implode(' ', $featurePolicy));
            
            // Cache Control
            if (!app()->environment('local')) {
                $response->headers->set('Cache-Control', 'max-age=31536000, public');
            }
            
            // Remove unwanted headers
            $response->headers->remove('X-Powered-By');
            $response->headers->remove('Server');
        }

        return $response;
    }
}
