<?php

namespace App\Repositories\Eloquent;

use App\Contracts\Repositories\PaymentRepositoryInterface;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Collection;

class EloquentPaymentRepository extends EloquentRepository implements PaymentRepositoryInterface
{
    /**
     * Create a new repository instance.
     */
    public function __construct(Payment $model)
    {
        $this->model = $model;
    }

    /**
     * Get payments by account.
     */
    public function getByAccount(int $accountId): Collection
    {
        return $this->model->where('account_id', $accountId)->latest()->get();
    }

    /**
     * Get payments by subscription.
     */
    public function getBySubscription(int $subscriptionId): Collection
    {
        return $this->model->where('subscription_id', $subscriptionId)->latest()->get();
    }

    /**
     * Get payments by status.
     */
    public function getByStatus(string $status): Collection
    {
        return $this->model->where('status', $status)->latest()->get();
    }

    /**
     * Get successful payments.
     */
    public function getSuccessfulPayments(): Collection
    {
        return $this->getByStatus('completed');
    }

    /**
     * Get failed payments.
     */
    public function getFailedPayments(): Collection
    {
        return $this->getByStatus('failed');
    }

    /**
     * Find by transaction ID.
     */
    public function findByTransactionId(string $transactionId): ?Payment
    {
        return $this->model->where('transaction_id', $transactionId)->first();
    }

    /**
     * Get payments in date range.
     */
    public function getPaymentsInDateRange(string $startDate, string $endDate): Collection
    {
        return $this->model
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get();
    }

    /**
     * Calculate total revenue.
     */
    public function calculateTotalRevenue(?string $startDate = null, ?string $endDate = null): float
    {
        $query = $this->model->where('status', 'completed');

        if ($startDate && $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }

        return (float) $query->sum('amount');
    }

    /**
     * Calculate revenue by package.
     */
    public function calculateRevenueByPackage(int $packageId): float
    {
        return (float) $this->model
            ->whereHas('subscription', function ($query) use ($packageId) {
                $query->where('package_id', $packageId);
            })
            ->where('status', 'completed')
            ->sum('amount');
    }

    /**
     * Get recent payments.
     */
    public function getRecentPayments(int $limit = 10): Collection
    {
        return $this->model->latest()->limit($limit)->get();
    }
}
