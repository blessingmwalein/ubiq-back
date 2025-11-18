<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'message' => 'Unauthenticated.',
            ], 401);
        }

        // Check if user has the required role
        if ($user->role !== $role) {
            return response()->json([
                'message' => 'Unauthorized. This action requires ' . $role . ' role.',
            ], 403);
        }

        return $next($request);
    }
}
