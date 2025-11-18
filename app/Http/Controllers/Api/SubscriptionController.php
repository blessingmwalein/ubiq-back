<?php

namespace App\Http\Controllers\Api;

use App\DTOs\CreateSubscriptionDTO;
use App\Http\Controllers\Controller;
use App\Repositories\Eloquent\EloquentAccountRepository;
use App\Services\BillingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    public function __construct(
        private BillingService $billingService,
        private EloquentAccountRepository $accountRepository
    ) {
    }

    /**
     * Get user's current subscription.
     */
    public function index(Request $request): JsonResponse
    {
        $account = $this->accountRepository->getPrimaryAccount($request->user()->id);

        if (!$account) {
            return response()->json([
                'message' => 'No account found',
            ], 404);
        }

        $subscription = $account->subscriptions()->with('package')->latest()->first();

        return response()->json([
            'data' => $subscription,
        ]);
    }

    /**
     * Create/upgrade subscription.
     */
    public function subscribe(Request $request): JsonResponse
    {
        $request->validate([
            'package_id' => 'required|exists:packages,id',
            'payment_method' => 'required|in:stripe,paypal,ecocash,inbucks',
            'payment_token' => 'nullable|string',
            'billing_cycle' => 'required|in:monthly,yearly',
        ]);

        try {
            $account = $this->accountRepository->getPrimaryAccount($request->user()->id);

            if (!$account) {
                return response()->json([
                    'message' => 'No account found',
                ], 404);
            }

            $dto = CreateSubscriptionDTO::fromRequest(array_merge(
                $request->all(),
                ['account_id' => $account->id]
            ));
            
            $subscription = $this->billingService->createSubscription($dto);

            return response()->json([
                'message' => 'Subscription created successfully',
                'data' => $subscription->load('package'),
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Cancel subscription.
     */
    public function cancel(Request $request): JsonResponse
    {
        $account = $this->accountRepository->getPrimaryAccount($request->user()->id);

        if (!$account) {
            return response()->json([
                'message' => 'No account found',
            ], 404);
        }

        $subscription = $account->subscriptions()->where('status', 'active')->latest()->first();

        if (!$subscription) {
            return response()->json([
                'message' => 'No active subscription found',
            ], 404);
        }

        $cancelled = $this->billingService->cancelSubscription($subscription->id);

        if (!$cancelled) {
            return response()->json([
                'message' => 'Failed to cancel subscription',
            ], 400);
        }

        return response()->json([
            'message' => 'Subscription cancelled successfully',
        ]);
    }

    /**
     * Renew subscription.
     */
    public function renew(Request $request): JsonResponse
    {
        try {
            $account = $this->accountRepository->getPrimaryAccount($request->user()->id);

            if (!$account) {
                return response()->json([
                    'message' => 'No account found',
                ], 404);
            }

            $subscription = $account->subscriptions()->latest()->first();

            if (!$subscription) {
                return response()->json([
                    'message' => 'No subscription found',
                ], 404);
            }

            $renewed = $this->billingService->renewSubscription($subscription->id);

            return response()->json([
                'message' => 'Subscription renewed successfully',
                'data' => $renewed->load('package'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 400);
        }
    }
}
