<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureRequestFromBrowser
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        // ✅ Allow preflight CORS requests (OPTIONS)
        if ($request->isMethod('OPTIONS')) {
            return response()->json(['status' => true, 'message' => 'CORS preflight OK']);
        }

        // ✅ Must include Bearer token
        $token = $request->bearerToken();
        if (!$token) {
            return response()->json(['status' => false, 'message' => 'Token missing'], 401);
        }

        // ✅ Detect if it's a real browser request
        $userAgent = strtolower($request->header('User-Agent', ''));

        // Common browser identifiers
        $browsers = ['mozilla', 'chrome', 'safari', 'firefox', 'edge', 'opr', 'msie', 'trident'];

        $isBrowser = false;
        foreach ($browsers as $browser) {
            if (strpos($userAgent, $browser) !== false) {
                $isBrowser = true;
                break;
            }
        }

        if (!$isBrowser) {
            return response()->json([
                'status' => false,
                'message' => 'Only browser-origin requests are allowed.'
            ], 403);
        }

        return $next($request);
    }
}
