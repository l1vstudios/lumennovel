<?php

namespace App\Http\Middleware;

use App\Support\ApiToken;
use Closure;

/**
 * Memproteksi setiap akses API dengan token berbasis waktu.
 *
 * Token diambil dari header "X-Api-Token" (atau "Authorization: Bearer ...").
 * Bila token tidak ada / tidak valid / sudah kedaluwarsa -> 403 Forbidden.
 */
class ApiTokenMiddleware
{
    public function handle($request, Closure $next)
    {
        // Lewatkan preflight CORS agar header bisa dikirim browser.
        if ($request->isMethod('OPTIONS')) {
            return $next($request);
        }

        $token = $request->header('X-Api-Token');

        if (!$token) {
            $auth = $request->header('Authorization', '');
            if (stripos($auth, 'Bearer ') === 0) {
                $token = substr($auth, 7);
            }
        }

        if (!ApiToken::verify($token)) {
            return response()->json([
                'status'  => false,
                'message' => 'Forbidden',
            ], 403);
        }

        return $next($request);
    }
}
