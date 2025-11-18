<?php

namespace App\Repositories\Eloquent;

use App\Contracts\Repositories\AccountRepositoryInterface;
use App\Models\Account;
use Illuminate\Database\Eloquent\Collection;

class EloquentAccountRepository extends EloquentRepository implements AccountRepositoryInterface
{
    /**
     * Create a new repository instance.
     */
    public function __construct(Account $model)
    {
        $this->model = $model;
    }

    /**
     * Get accounts by user ID.
     */
    public function getByUserId(int $userId): Collection
    {
        return $this->model->where('user_id', $userId)->get();
    }

    /**
     * Get primary account for user.
     */
    public function getPrimaryAccount(int $userId): ?Account
    {
        return $this->model
            ->where('user_id', $userId)
            ->first();
    }

    /**
     * Get active accounts.
     */
    public function getActiveAccounts(): Collection
    {
        return $this->model->where('status', 'active')->get();
    }

    /**
     * Get accounts by package.
     */
    public function getByPackageId(int $packageId): Collection
    {
        return $this->model->where('package_id', $packageId)->get();
    }

    /**
     * Get accounts by status.
     */
    public function getByStatus(string $status): Collection
    {
        return $this->model->where('status', $status)->get();
    }

    /**
     * Update account status.
     */
    public function updateStatus(int $accountId, string $status): bool
    {
        return $this->update($accountId, ['status' => $status]);
    }

    /**
     * Get accounts with active subscriptions.
     */
    public function getAccountsWithActiveSubscriptions(): Collection
    {
        return $this->model
            ->whereHas('subscriptions', function ($query) {
                $query->where('status', 'active');
            })
            ->get();
    }
}
