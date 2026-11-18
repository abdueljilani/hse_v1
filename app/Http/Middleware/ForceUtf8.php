<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ForceUtf8
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $contentType = $response->headers->get('Content-Type');
        if ($contentType && str_contains($contentType, 'text/html')) {
            if (!str_contains(strtolower($contentType), 'charset=utf-8')) {
                $response->headers->set('Content-Type', 'text/html; charset=UTF-8');
            }
        }

        // Prevent browser caching of HTML pages
        $response->headers->set('Cache-Control', 'no-cache, no-store, must-revalidate');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');

        return $response;
    }
}