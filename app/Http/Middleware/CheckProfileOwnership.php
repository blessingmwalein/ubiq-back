<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckProfileOwnership
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $profileId = $request->route('profileId') ?? $request->input('profile_id');

        if (!$profileId) {
            return $next($request);
        }

        // Get user's accounts
        $userAccountIds = $user->accounts()->pluck('id')->toArray();

        // Check if profile belongs to one of user's accounts
        $profile = \App\Models\Profile::find($profileId);

        if (!$profile || !in_array($profile->account_id, $userAccountIds)) {
            return response()->json([
                'message' => 'You do not have access to this profile.',
            ], 403);
        }

        return $next($request);
    }
}
