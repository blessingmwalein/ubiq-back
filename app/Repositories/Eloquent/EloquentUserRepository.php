<?php

namespace App\Repositories\Eloquent;

use App\Contracts\Repositories\UserRepositoryInterface;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class EloquentUserRepository extends EloquentRepository implements UserRepositoryInterface
{
    /**
     * Create a new repository instance.
     */
    public function __construct(User $model)
    {
        $this->model = $model;
    }

    /**
     * Find user by email.
     */
    public function findByEmail(string $email): ?User
    {
        return $this->model->where('email', $email)->first();
    }

    /**
     * Find user by social provider.
     */
    public function findBySocialProvider(string $provider, string $providerId): ?User
    {
        return $this->model
            ->where('social_provider', $provider)
            ->where('social_provider_id', $providerId)
            ->first();
    }

    /**
     * Get users by role.
     */
    public function getUsersByRole(string $role): Collection
    {
        return $this->model->where('role', $role)->get();
    }

    /**
     * Get users on trial.
     */
    public function getUsersOnTrial(): Collection
    {
        return $this->model
            ->whereNotNull('trial_ends_at')
            ->where('trial_ends_at', '>', now())
            ->get();
    }

    /**
     * Get admin users.
     */
    public function getAdmins(): Collection
    {
        return $this->getUsersByRole('admin');
    }

    /**
     * Get content provider users.
     */
    public function getProviders(): Collection
    {
        return $this->getUsersByRole('provider');
    }

    /**
     * Update trial end date.
     */
    public function updateTrialEndDate(int $userId, ?\DateTime $trialEndsAt): bool
    {
        return $this->update($userId, ['trial_ends_at' => $trialEndsAt]);
    }
}
