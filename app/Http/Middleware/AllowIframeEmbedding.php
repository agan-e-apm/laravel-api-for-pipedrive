<?php

namespace App\Http\Middleware;

use Closure;

class AllowIframeEmbedding
{
    public function handle($request, Closure $next)
    {
        $response = $next($request);

        // Remove or override X-Frame-Options header
        $response->headers->remove('X-Frame-Options');

        // Optionally add CSP frame-ancestors if needed:
        // $response->headers->set('Content-Security-Policy', "frame-ancestors 'self' https://*.pipedrive.com");

        return $response;
    }
}
