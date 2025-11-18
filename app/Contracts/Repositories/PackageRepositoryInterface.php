<?php

namespace App\Contracts\Repositories;

use App\Models\Package;
use Illuminate\Database\Eloquent\Collection;

interface PackageRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Get all active packages.
     */
    public function getActivePackages(): Collection;

    /**
     * Get package by code.
     */
    public function findByCode(string $code): ?Package;

    /**
     * Get featured packages.
     */
    public function getFeaturedPackages(): Collection;

    /**
     * Get packages by type.
     */
    public function getByType(string $type): Collection;

    /**
     * Count active subscriptions for package.
     */
    public function countActiveSubscriptions(int $packageId): int;
}
