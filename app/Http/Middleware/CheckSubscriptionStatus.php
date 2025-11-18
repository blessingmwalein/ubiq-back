<?php

namespace App\Http\Middleware;

use App\Contracts\Repositories\SubscriptionRepositoryInterface;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckSubscriptionStatus
{
    public function __construct(
        private SubscriptionRepositoryInterface $subscriptionRepository
    ) {
    }

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'message' => 'Unauthenticated.',
            ], 401);
        }

        // Get user's primary account
        $account = $user->account;

        if (!$account) {
            return response()->json([
                'message' => 'No account found.',
            ], 403);
        }

        // Check for active subscription
        $activeSubscription = $this->subscriptionRepository->getActiveSubscription($account->id);

        if (!$activeSubscription) {
            // Check if user is on trial
            if ($user->trial_ends_at && $user->trial_ends_at->isFuture()) {
                return $next($request);
            }

            return response()->json([
                'message' => 'Active subscription required.',
                'error' => 'subscription_required',
            ], 403);
        }

        return $next($request);
    }
}
