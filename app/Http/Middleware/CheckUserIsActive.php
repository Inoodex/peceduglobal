<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckUserIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && !$user->is_active) {
            auth('api')->logout();
            return response()->json([
                'success' => false,
                'message' => 'Your account has been deactivated. Contact an administrator.',
            ], 403);
        }

        return $next($request);
    }
}
