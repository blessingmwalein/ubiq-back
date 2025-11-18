<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\BillingService;
use Illuminate\Http\JsonResponse;

class PackageController extends Controller
{
    public function __construct(
        private BillingService $billingService
    ) {
    }

    /**
     * Get all active packages.
     */
    public function index(): JsonResponse
    {
        $packages = $this->billingService->getActivePackages();

        return response()->json([
            'data' => $packages,
        ]);
    }

    /**
     * Get package details.
     */
    public function show(int $id): JsonResponse
    {
        $package = $this->billingService->getPackage($id);

        if (!$package) {
            return response()->json([
                'message' => 'Package not found',
            ], 404);
        }

        return response()->json([
            'data' => $package,
        ]);
    }
}
