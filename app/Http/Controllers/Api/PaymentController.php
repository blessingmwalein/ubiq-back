<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\BillingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function __construct(
        private BillingService $billingService
    ) {
    }

    /**
     * Get payment history for account.
     */
    public function history(Request $request): JsonResponse
    {
        $request->validate([
            'account_id' => 'required|exists:accounts,id',
        ]);

        $payments = $this->billingService->getPaymentHistory($request->account_id);

        return response()->json([
            'data' => $payments,
        ]);
    }

    /**
     * Get payment details.
     */
    public function show(int $id): JsonResponse
    {
        $payment = $this->billingService->getPayment($id);

        if (!$payment) {
            return response()->json([
                'message' => 'Payment not found',
            ], 404);
        }

        return response()->json([
            'data' => $payment,
        ]);
    }
}
