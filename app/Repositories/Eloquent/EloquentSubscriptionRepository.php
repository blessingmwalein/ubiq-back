<?php

namespace App\Repositories\Eloquent;

use App\Contracts\Repositories\SubscriptionRepositoryInterface;
use App\Models\Subscription;
use Illuminate\Database\Eloquent\Collection;

class EloquentSubscriptionRepository extends EloquentRepository implements SubscriptionRepositoryInterface
{
    /**
     * Create a new repository instance.
     */
    public function __construct(Subscription $model)
    {
        $this->model = $model;
    }

    /**
     * Get active subscription for account.
     */
    public function getActiveSubscription(int $accountId): ?Subscription
    {
        return $this->model
            ->where('account_id', $accountId)
            ->where('status', 'active')
            ->where('ends_at', '>', now())
            ->first();
    }

    /**
     * Get subscriptions by account.
     */
    public function getByAccount(int $accountId): Collection
    {
        return $this->model->where('account_id', $accountId)->get();
    }

    /**
     * Get subscriptions by package.
     */
    public function getByPackage(int $packageId): Collection
    {
        return $this->model->where('package_id', $packageId)->get();
    }

    /**
     * Get active subscriptions.
     */
    public function getActiveSubscriptions(): Collection
    {
        return $this->model
            ->where('status', 'active')
            ->where('ends_at', '>', now())
            ->get();
    }

    /**
     * Get expiring subscriptions.
     */
    public function getExpiringSubscriptions(int $days = 7): Collection
    {
        return $this->model
            ->where('status', 'active')
            ->whereBetween('ends_at', [now(), now()->addDays($days)])
            ->get();
    }

    /**
     * Find by provider subscription ID.
     */
    public function findByProviderSubscriptionId(string $providerId): ?Subscription
    {
        return $this->model->where('provider_subscription_id', $providerId)->first();
    }

    /**
     * Cancel subscription.
     */
    public function cancel(int $subscriptionId): bool
    {
        $subscription = $this->find($subscriptionId);
        
        if (!$subscription) {
            return false;
        }

        return $subscription->cancel();
    }

    /**
     * Update subscription status.
     */
    public function updateStatus(int $subscriptionId, string $status): bool
    {
        return $this->update($subscriptionId, ['status' => $status]);
    }

    /**
     * Get trial subscriptions.
     */
    public function getTrialSubscriptions(): Collection
    {
        return $this->model->where('is_trial', true)->get();
    }

    /**
     * Count active subscriptions by package.
     */
    public function countActiveByPackage(int $packageId): int
    {
        return $this->model
            ->where('package_id', $packageId)
            ->where('status', 'active')
            ->where('ends_at', '>', now())
            ->count();
    }
}
