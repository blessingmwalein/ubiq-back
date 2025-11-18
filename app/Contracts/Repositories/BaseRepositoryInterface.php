<?php

namespace App\Contracts\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

interface BaseRepositoryInterface
{
    /**
     * Find a model by ID.
     */
    public function find(int $id): ?Model;

    /**
     * Find a model by UUID.
     */
    public function findByUuid(string $uuid): ?Model;

    /**
     * Get all models.
     */
    public function all(array $columns = ['*']): Collection;

    /**
     * Create a new model.
     */
    public function create(array $data): Model;

    /**
     * Update a model.
     */
    public function update(int $id, array $data): bool;

    /**
     * Delete a model.
     */
    public function delete(int $id): bool;

    /**
     * Paginate models.
     */
    public function paginate(int $perPage = 15, array $columns = ['*']): LengthAwarePaginator;

    /**
     * Find models by criteria.
     */
    public function findWhere(array $criteria): Collection;

    /**
     * Find first model by criteria.
     */
    public function findFirstWhere(array $criteria): ?Model;
}
