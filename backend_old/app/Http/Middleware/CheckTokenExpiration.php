<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckTokenExpiration
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // token=time: expires_at в future
        $token = $user->currentAccessToken();

        if (!$token || ($token->expires_at && $token->expires_at->isPast())) {
            // buybuy token
            return response()->json(['message' => 'Token expired'], 401);
        }

        return $next($request);
    }
}
