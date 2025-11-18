<?php

namespace App\Services;

use App\Contracts\Repositories\PackageRepositoryInterface;
use App\Contracts\Repositories\PaymentRepositoryInterface;
use App\Contracts\Repositories\SubscriptionRepositoryInterface;
use App\DTOs\CreateSubscriptionDTO;
use App\Models\Package;
use App\Models\Payment;
use App\Models\Subscription;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class BillingService
{
    public function __construct(
        private SubscriptionRepositoryInterface $subscriptionRepository,
        private PaymentRepositoryInterface $paymentRepository,
        private PackageRepositoryInterface $packageRepository,
    ) {
    }

    /**
     * Get all active packages.
     */
    public function getActivePackages(): Collection
    {
        return $this->packageRepository->getActivePackages();
    }

    /**
     * Get package details.
     */
    public function getPackage(int $packageId): ?Package
    {
        return $this->packageRepository->find($packageId);
    }

    /**
     * Get package by code.
     */
    public function getPackageByCode(string $code): ?Package
    {
        return $this->packageRepository->findByCode($code);
    }

    /**
     * Create subscription.
     */
    public function createSubscription(CreateSubscriptionDTO $dto): Subscription
    {
        return DB::transaction(function () use ($dto) {
            $package = $this->packageRepository->find($dto->packageId);

            if (!$package) {
                throw new \Exception('Package not found.');
            }

            // Calculate dates based on billing cycle
            $startsAt = now();
            $endsAt = match ($package->billing_cycle) {
                'monthly' => $startsAt->copy()->addMonth(),
                'yearly' => $startsAt->copy()->addYear(),
                default => $startsAt->copy()->addMonth(),
            };

            // Create subscription
            $subscription = $this->subscriptionRepository->create([
                'account_id' => $dto->accountId,
                'package_id' => $dto->packageId,
                'status' => 'active',
                'starts_at' => $startsAt,
                'ends_at' => $endsAt,
                'is_trial' => $dto->isTrial,
                'auto_renew' => true,
            ]);

            // Create payment record if not trial
            if (!$dto->isTrial) {
                $this->processPayment(
                    $subscription->id,
                    $dto->accountId,
                    $package->price,
                    $dto->paymentMethod,
                    $dto->paymentToken
                );
            }

            return $subscription->fresh();
        });
    }

    /**
     * Cancel subscription.
     */
    public function cancelSubscription(int $subscriptionId): bool
    {
        return $this->subscriptionRepository->cancel($subscriptionId);
    }

    /**
     * Renew subscription.
     */
    public function renewSubscription(int $subscriptionId): Subscription
    {
        return DB::transaction(function () use ($subscriptionId) {
            $subscription = $this->subscriptionRepository->find($subscriptionId);

            if (!$subscription) {
                throw new \Exception('Subscription not found.');
            }

            $package = $this->packageRepository->find($subscription->package_id);

            // Calculate new end date
            $newEndsAt = match ($package->billing_cycle) {
                'monthly' => now()->addMonth(),
                'yearly' => now()->addYear(),
                default => now()->addMonth(),
            };

            // Update subscription
            $subscription->update([
                'status' => 'active',
                'starts_at' => now(),
                'ends_at' => $newEndsAt,
                'is_trial' => false,
            ]);

            // Process payment
            $this->processPayment(
                $subscription->id,
                $subscription->account_id,
                $package->price,
                'stripe'
            );

            return $subscription->fresh();
        });
    }

    /**
     * Process payment.
     */
    public function processPayment(
        int $subscriptionId,
        int $accountId,
        float $amount,
        string $paymentMethod = 'stripe',
        ?string $paymentToken = null
    ): Payment {
        // Here you would integrate with actual payment gateway (Stripe, PayPal, etc.)
        // For now, we'll create a successful payment record
        
        return $this->paymentRepository->create([
            'subscription_id' => $subscriptionId,
            'account_id' => $accountId,
            'amount' => $amount,
            'currency' => 'USD',
            'payment_method' => $paymentMethod,
            'transaction_id' => 'txn_' . uniqid(),
            'status' => 'completed',
            'metadata' => [
                'payment_token' => $paymentToken,
                'processed_at' => now()->toIso8601String(),
            ],
        ]);
    }

    /**
     * Get active subscription for account.
     */
    public function getActiveSubscription(int $accountId): ?Subscription
    {
        return $this->subscriptionRepository->getActiveSubscription($accountId);
    }

    /**
     * Get payment history for account.
     */
    public function getPaymentHistory(int $accountId): Collection
    {
        return $this->paymentRepository->getByAccount($accountId);
    }

    /**
     * Get payment details.
     */
    public function getPayment(int $paymentId): ?Payment
    {
        return $this->paymentRepository->find($paymentId);
    }

    /**
     * Get subscriptions by account.
     */
    public function getSubscriptionsByAccount(int $accountId): Collection
    {
        return $this->subscriptionRepository->getByAccount($accountId);
    }

    /**
     * Get expiring subscriptions.
     */
    public function getExpiringSubscriptions(int $days = 7): Collection
    {
        return $this->subscriptionRepository->getExpiringSubscriptions($days);
    }

    /**
     * Calculate total revenue.
     */
    public function calculateTotalRevenue(?string $startDate = null, ?string $endDate = null): float
    {
        return $this->paymentRepository->calculateTotalRevenue($startDate, $endDate);
    }

    /**
     * Get revenue by package.
     */
    public function getRevenueByPackage(int $packageId): float
    {
        return $this->paymentRepository->calculateRevenueByPackage($packageId);
    }
}
