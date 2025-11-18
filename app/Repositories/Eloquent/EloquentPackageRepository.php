<?php

namespace App\Repositories\Eloquent;

use App\Contracts\Repositories\PackageRepositoryInterface;
use App\Models\Package;
use Illuminate\Database\Eloquent\Collection;

class EloquentPackageRepository extends EloquentRepository implements PackageRepositoryInterface
{
    /**
     * Create a new repository instance.
     */
    public function __construct(Package $model)
    {
        $this->model = $model;
    }

    /**
     * Get all active packages.
     */
    public function getActivePackages(): Collection
    {
        return $this->model->where('is_active', true)->orderBy('price_monthly')->get();
    }

    /**
     * Get package by code.
     */
    public function findByCode(string $code): ?Package
    {
        return $this->model->where('code', $code)->first();
    }

    /**
     * Get featured packages.
     */
    public function getFeaturedPackages(): Collection
    {
        return $this->model
            ->where('is_active', true)
            ->where('is_featured', true)
            ->orderBy('price_monthly')
            ->get();
    }

    /**
     * Get packages by type.
     */
    public function getByType(string $type): Collection
    {
        return $this->model
            ->where('type', $type)
            ->where('is_active', true)
            ->orderBy('price_monthly')
            ->get();
    }

    /**
     * Count active subscriptions for package.
     */
    public function countActiveSubscriptions(int $packageId): int
    {
        return $this->model
            ->find($packageId)
            ?->subscriptions()
            ->where('status', 'active')
            ->where('ends_at', '>', now())
            ->count() ?? 0;
    }
}
