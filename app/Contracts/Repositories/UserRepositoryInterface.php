<?php

namespace App\Contracts\Repositories;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

interface UserRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Find user by email.
     */
    public function findByEmail(string $email): ?User;

    /**
     * Find user by social provider credentials.
     */
    public function findBySocialProvider(string $provider, string $providerId): ?User;

    /**
     * Get users by role.
     */
    public function getUsersByRole(string $role): Collection;

    /**
     * Get users on trial.
     */
    public function getUsersOnTrial(): Collection;

    /**
     * Get admins.
     */
    public function getAdmins(): Collection;

    /**
     * Get providers.
     */
    public function getProviders(): Collection;

    /**
     * Update user's trial end date.
     */
    public function updateTrialEndDate(int $userId, \DateTime $endDate): bool;
}
