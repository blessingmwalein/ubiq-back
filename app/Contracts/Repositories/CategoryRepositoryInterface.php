<?php

namespace App\Contracts\Repositories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Collection;

interface CategoryRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Get all active categories.
     */
    public function getActiveCategories(): Collection;

    /**
     * Get category by slug.
     */
    public function findBySlug(string $slug): ?Category;

    /**
     * Get parent categories.
     */
    public function getParentCategories(): Collection;

    /**
     * Get child categories.
     */
    public function getChildCategories(int $parentId): Collection;

    /**
     * Get category with content count.
     */
    public function getCategoriesWithContentCount(): Collection;

    /**
     * Reorder categories.
     */
    public function reorder(array $categoryIds): bool;
}
