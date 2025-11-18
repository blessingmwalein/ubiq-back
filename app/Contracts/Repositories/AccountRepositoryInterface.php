<?php

namespace App\Contracts\Repositories;

use App\Models\Account;
use Illuminate\Database\Eloquent\Collection;

interface AccountRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Get accounts by user ID.
     */
    public function getByUserId(int $userId): Collection;

    /**
     * Get user's primary account.
     */
    public function getPrimaryAccount(int $userId): ?Account;

    /**
     * Get active accounts.
     */
    public function getActiveAccounts(): Collection;

    /**
     * Get accounts by package ID.
     */
    public function getByPackageId(int $packageId): Collection;

    /**
     * Get accounts by status.
     */
    public function getByStatus(string $status): Collection;

    /**
     * Update account status.
     */
    public function updateStatus(int $accountId, string $status): bool;

    /**
     * Get accounts with active subscriptions.
     */
    public function getAccountsWithActiveSubscriptions(): Collection;
}
