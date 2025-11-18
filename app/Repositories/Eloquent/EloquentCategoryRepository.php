<?php

namespace App\Repositories\Eloquent;

use App\Contracts\Repositories\CategoryRepositoryInterface;
use App\Models\Category;
use Illuminate\Database\Eloquent\Collection;

class EloquentCategoryRepository extends EloquentRepository implements CategoryRepositoryInterface
{
    /**
     * Create a new repository instance.
     */
    public function __construct(Category $model)
    {
        $this->model = $model;
    }

    /**
     * Get all active categories.
     */
    public function getActiveCategories(): Collection
    {
        return $this->model
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();
    }

    /**
     * Get category by slug.
     */
    public function findBySlug(string $slug): ?Category
    {
        return $this->model->where('slug', $slug)->first();
    }

    /**
     * Get parent categories.
     */
    public function getParentCategories(): Collection
    {
        return $this->model
            ->whereNull('parent_id')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();
    }

    /**
     * Get child categories.
     */
    public function getChildCategories(int $parentId): Collection
    {
        return $this->model
            ->where('parent_id', $parentId)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();
    }

    /**
     * Get category with content count.
     */
    public function getCategoriesWithContentCount(): Collection
    {
        return $this->model
            ->withCount(['contentItems' => function ($query) {
                $query->where('visibility', 'public')->whereNotNull('published_at');
            }])
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();
    }

    /**
     * Reorder categories.
     */
    public function reorder(array $categoryIds): bool
    {
        foreach ($categoryIds as $order => $categoryId) {
            $this->update($categoryId, ['sort_order' => $order]);
        }

        return true;
    }
}
