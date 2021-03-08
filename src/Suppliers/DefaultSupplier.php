<?php

namespace Nevadskiy\Geonames\Suppliers;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Nevadskiy\Geonames\Models\Continent;
use Nevadskiy\Geonames\Support\Batch\Batch;

abstract class DefaultSupplier implements Supplier
{
    /**
     * Insert cities batch to reduce queries amount to be performed.
     *
     * @var Batch
     */
    protected $insertBatch;

    /**
     * Fields of the entity table.
     *
     * @var array
     */
    protected $fields;

    /**
     * Make a new seeder instance.
     */
    public function __construct(int $batchSize = 1000)
    {
        $this->insertBatch = $this->makeInsertBatch($batchSize);
    }

    /**
     * @inheritDoc
     */
    public function init(): void
    {
        $this->fields = $this->getFields();
    }

    /**
     * @inheritDoc
     */
    public function commit(): void
    {
        $this->insertBatch->commit();
    }

    /**
     * Make a batch instance for better inserting performance.
     */
    protected function makeInsertBatch(int $batchSize): Batch
    {
        return new Batch(function (array $data) {
            DB::table($this->getTable())->insert($data);
        }, $batchSize);
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
     * Insert the item into database.
     *
     * @param array $data
     * @param int $id
     * @return bool
     */
    protected function performInsert(array $data, int $id): bool
    {
        $this->insertBatch->push(
            $this->resolveValues($this->mapInsertFields($data, $id))
        );

        return true;
    }

    /**
     * Find a model by the given geonames' id.
     *
     * @param int $id
     * @return Continent|null
     */
    protected function findModel(int $id): ?Model
    {
        return $this->getModel()
            ->newQuery()
            ->where('geoname_id', $id)
            ->first();
    }

    /**
     * Update the model with the given data.
     *
     * @param Model $model
     * @param array $data
     * @return bool
     */
    protected function updateModel(Model $model, array $data, int $id): bool
    {
        return $model->update(
            $this->resolveValues($this->mapUpdateFields($data, $id))
        );
    }

    /**
     * Delete the given model from the database.
     *
     * @param Model $model
     * @return bool
     * @throws Exception
     */
    protected function deleteModel(Model $model): bool
    {
        return $model->delete();
    }

    /**
     * Get fields of the entity table.
     */
    protected function getFields(): array
    {
        return DB::getSchemaBuilder()->getColumnListing($this->getTable());
    }

    /**
     * Get a table name of the supplier model.
     */
    protected function getTable(): string
    {
        return $this->getModel()->getTable();
    }

    /**
     * Resolve the attributes.
     *
     * @param array $data
     * @return array
     */
    protected function resolveValues(array $data): array
    {
        $values = [];

        foreach (Arr::only($data, $this->fields) as $attribute => $value) {
            $values[$attribute] = value($value);
        }

        return $values;
    }

    /**
     * Get the model for the supplier.
     */
    abstract protected function getModel(): Model;

    /**
     * Determine if the given data should be supplied.
     */
    abstract protected function shouldSupply(array $data, int $id): bool;

    /**
     * Map insert fields for the supplier model.
     */
    abstract protected function mapInsertFields(array $data, int $id): array;

    /**
     * Map update fields for the model.
     */
    abstract protected function mapUpdateFields(array $data, int $id): array;
}
