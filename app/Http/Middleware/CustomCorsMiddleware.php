<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CustomCorsMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $origin = $request->headers->get('Origin') ?? '*';

        // If the request is OPTIONS (preflight), return a quick 200 response
        if ($request->getMethod() === 'OPTIONS') {
            return response()->json('OK', 200, [
                'Access-Control-Allow-Origin' => $origin,
                'Access-Control-Allow-Methods' => 'GET, POST, PUT, PATCH, DELETE, OPTIONS',
                'Access-Control-Allow-Headers' => 'Content-Type, Authorization, X-Requested-With, Accept, Origin',
                'Access-Control-Allow-Credentials' => 'true',
            ]);
        }

        // Continue normal flow for other requests
        $response = $next($request);

        $response->headers->set('Access-Control-Allow-Origin', $origin);
        $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS');
        $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With, Accept, Origin');
        $response->headers->set('Access-Control-Allow-Credentials', 'true');

        return $response;
    }
}
