<?php

namespace App\Repositories\Eloquent;

use App\Contracts\Repositories\BaseRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

abstract class EloquentRepository implements BaseRepositoryInterface
{
    /**
 * The model instance.
     */
    protected Model $model;

    /**
     * Find a record by ID.
     */
    public function find(int $id): ?Model
    {
        return $this->model->find($id);
    }

    /**
     * Find a record by UUID.
     */
    public function findByUuid(string $uuid): ?Model
    {
        return $this->model->where('uuid', $uuid)->first();
    }

    /**
     * Get all records.
     */
    public function all(array $columns = ['*']): Collection
    {
        return $this->model->all($columns);
    }

    /**
     * Create a new record.
     */
    public function create(array $data): Model
    {
        return $this->model->create($data);
    }

    /**
     * Update a record.
     */
    public function update(int $id, array $data): bool
    {
        $record = $this->find($id);
        
        if (!$record) {
            return false;
        }

        return $record->update($data);
    }

    /**
     * Delete a record.
     */
    public function delete(int $id): bool
    {
        $record = $this->find($id);
        
        if (!$record) {
            return false;
        }

        return $record->delete();
    }

    /**
     * Paginate records.
     */
    public function paginate(int $perPage = 15, array $columns = ['*']): LengthAwarePaginator
    {
        return $this->model->paginate($perPage, $columns);
    }

    /**
     * Find records by criteria.
     */
    public function findWhere(array $criteria): Collection
    {
        $query = $this->model->query();

        foreach ($criteria as $field => $value) {
            if (is_array($value)) {
                $query->whereIn($field, $value);
            } else {
                $query->where($field, $value);
            }
        }

        return $query->get();
    }

    /**
     * Find first record by criteria.
     */
    public function findFirstWhere(array $criteria): ?Model
    {
        $query = $this->model->query();

        foreach ($criteria as $field => $value) {
            $query->where($field, $value);
        }

        return $query->first();
    }

    /**
     * Get the model instance.
     */
    public function getModel(): Model
    {
        return $this->model;
    }

    /**
     * Set the model instance.
     */
    public function setModel(Model $model): self
    {
        $this->model = $model;
        
        return $this;
    }
}
