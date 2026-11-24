<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/** Autentikasi API server-ke-server lewat header "Authorization: Bearer <token>". */
class VsmApiToken
{
    public function handle(Request $request, Closure $next)
    {
        if (config('vsm_api.open')) {
            return $next($request);
        }
        $given = (string) $request->bearerToken();
        $ok = false;
        foreach ((array) config('vsm_api.tokens') as $token) {
            if ($given !== '' && hash_equals($token, $given)) {
                $ok = true;
            }
        }
        if (!$ok) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated.'], 401, ['WWW-Authenticate' => 'Bearer']);
        }
        return $next($request);
    }
}