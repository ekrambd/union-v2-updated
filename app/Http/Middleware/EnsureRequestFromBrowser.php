<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureRequestFromBrowser
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
        $token = $request->bearerToken();
        if (!$token) {
            return response()->json(['status' => false, 'message' => 'Token missing'], 401);
        }

        // Check if request comes from browser
        $userAgent = $request->header('User-Agent') ?? '';
        if (strpos($userAgent, 'Mozilla') === false) {
            return response()->json(['status' => false, 'message' => 'Only browser requests allowed'], 403);
        }

        return $next($request);
    }
}
