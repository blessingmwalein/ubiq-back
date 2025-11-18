<?php

namespace App\Contracts\Repositories;

use App\Models\Subscription;
use Illuminate\Database\Eloquent\Collection;

interface SubscriptionRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Get active subscription for account.
     */
    public function getActiveSubscription(int $accountId): ?Subscription;

    /**
     * Get subscriptions by account.
     */
    public function getByAccount(int $accountId): Collection;

    /**
     * Get subscriptions by package.
     */
    public function getByPackage(int $packageId): Collection;

    /**
     * Get active subscriptions.
     */
    public function getActiveSubscriptions(): Collection;

    /**
     * Get expiring subscriptions.
     */
    public function getExpiringSubscriptions(int $days = 7): Collection;

    /**
     * Find by provider subscription ID.
     */
    public function findByProviderSubscriptionId(string $providerId): ?Subscription;

    /**
     * Cancel subscription.
     */
    public function cancel(int $subscriptionId): bool;

    /**
     * Update subscription status.
     */
    public function updateStatus(int $subscriptionId, string $status): bool;

    /**
     * Get trial subscriptions.
     */
    public function getTrialSubscriptions(): Collection;

    /**
     * Count active subscriptions by package.
     */
    public function countActiveByPackage(int $packageId): int;
}
