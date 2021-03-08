<?php

namespace Nevadskiy\Geonames\Suppliers;

use Closure;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Nevadskiy\Geonames\Models\Continent;

abstract class DefaultSupplier implements Supplier
{
    /**
     * Fields of the entity table.
     *
     * @var array
     */
    protected $fields;

    /**
     * Init the supplier.
     */
    public function init(): void
    {
        $this->fields = $this->getFields();
    }

    /**
     * @inheritDoc
     */
    public function insert(int $id, array $data): bool
    {
        if (! $this->shouldSupply($data, $id)) {
            return false;
        }

        return $this->performInsert($data, $id);
    }

    /**
     * @inheritDoc
     */
    public function modify(int $id, array $data): bool
    {
        $model = $this->findModel($id);

        if (! $model) {
            return $this->insert($id, $data);
        }

        if (! $this->shouldSupply($data, $id)) {
            return $this->deleteModel($model);
        }

        return $this->updateModel($model, $data, $id);
    }

    /**
     * @inheritDoc
     */
    public function delete(int $id, array $data): bool
    {
        $model = $this->findModel($id);

        if (! $model) {
            return false;
        }

        return $this->deleteModel($model);
    }

    /**
     * Resolve the attributes.
     *
     * @param array $data
     * @return array
     */
    protected function resolveValues(array $data, array $fields): array
    {
        $values = [];

        foreach (Arr::only($data, $fields) as $attribute => $value) {
              $values[$attribute] = $value instanceof Closure ? $value() : $value;
        }

        return $values;
    }

    /**
     * Determine if the given data should be supplied.
     */
    abstract protected function shouldSupply(array $data, int $id): bool;

    /**
     * Insert the item into database.
     *
     * @param array $data
     * @param int $id
     * @return bool
     */
    abstract protected function performInsert(array $data, int $id): bool;

    /**
     * Find a model by the given geonames' id.
     *
     * @param int $id
     * @return Continent|null
     */
    abstract protected function findModel(int $id): ?Model;

    /**
     * Update the model with the given data.
     *
     * @param Model $model
     * @param array $data
     * @return bool
     */
    abstract protected function updateModel(Model $model, array $data, int $id): bool;

    /**
     * Delete the given model from the database.
     *
     * @param Model $model
     * @return bool
     * @throws Exception
     */
    abstract protected function deleteModel(Model $model): bool;

    /**
     * Get fields of the entity table.
     */
    protected function getFields(): array
    {
        return DB::getSchemaBuilder()->getColumnListing($this->getTableName());
    }

    /**
     * Get table name of the entity.
     *
     * @return string
     */
    abstract protected function getTableName(): string;
}
