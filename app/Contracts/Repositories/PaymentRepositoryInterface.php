<?php

namespace App\Contracts\Repositories;

use App\Models\Payment;
use Illuminate\Database\Eloquent\Collection;

interface PaymentRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Get payments by account.
     */
    public function getByAccount(int $accountId): Collection;

    /**
     * Get payments by subscription.
     */
    public function getBySubscription(int $subscriptionId): Collection;

    /**
     * Get payments by status.
     */
    public function getByStatus(string $status): Collection;

    /**
     * Get successful payments.
     */
    public function getSuccessfulPayments(): Collection;

    /**
     * Get failed payments.
     */
    public function getFailedPayments(): Collection;

    /**
     * Find by transaction ID.
     */
    public function findByTransactionId(string $transactionId): ?Payment;

    /**
     * Get payments in date range.
     */
    public function getPaymentsInDateRange(string $startDate, string $endDate): Collection;

    /**
     * Calculate total revenue.
     */
    public function calculateTotalRevenue(?string $startDate = null, ?string $endDate = null): float;

    /**
     * Calculate revenue by package.
     */
    public function calculateRevenueByPackage(int $packageId): float;

    /**
     * Get recent payments.
     */
    public function getRecentPayments(int $limit = 10): Collection;
}
