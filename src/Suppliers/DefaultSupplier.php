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
     * The insert batch to reduce queries amount to be performed.
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
     * {@inheritdoc}
     */
    public function insertMany(iterable $data): void
    {
        $this->init();

        foreach ($data as $item) {
            $this->insert($item['geonameid'], $item);
        }

        $this->commit();
    }

    /**
     * {@inheritdoc}
     */
    public function modifyMany(iterable $data): void
    {
        // TODO: log changes...
        $this->getModel()::updating(function (Model $model) {
            $diff = [];

            foreach ($model->getDirty() as $field => $value) {
                $diff[$field] = [
                    'old' => (string) $model->getOriginal($field),
                    'new' => (string) $value,
                ];
            }

            dump($diff);
        });

        $this->init();

        foreach ($data as $item) {
            $this->modify($item['geonameid'], $item);
        }

        $this->commit();
    }

    /**
     * {@inheritdoc}
     */
    public function deleteMany(iterable $data): void
    {
        foreach ($data as $item) {
            $this->delete($item['geonameid']);
        }
    }

    /**
     * Init the supplier process.
     */
    protected function init(): void
    {
        $this->insertBatch = $this->makeInsertBatch();
        $this->fields = $this->getFields();
    }

    /**
     * Make a batch instance for better inserting performance.
     */
    protected function makeInsertBatch(int $batchSize = 1000): Batch
    {
        return new Batch(function (array $data) {
            DB::table($this->getTable())->insert($data);
        }, $batchSize);
    }

    /**
     * Commit the supplier process.
     */
    protected function commit(): void
    {
        $this->insertBatch->commit();
    }

    /**
     * Attempt to insert geonames data and return true on success.
     */
    protected function insert(int $id, array $item): bool
    {
        if (! $this->shouldSupply($item, $id)) {
            return false;
        }

        echo "Adding new model {$this->getModel()->getMorphClass()}: {$id}\n";

        return $this->performInsert($item, $id);
    }

    /**
     * Attempt to modify a geonames data by the given id and return true on success.
     */
    protected function modify(int $id, array $item): bool
    {
        $model = $this->findModel($id);

        if (! $model) {
            return $this->insert($id, $item);
        }

        if (! $this->shouldSupply($item, $id)) {
            return $this->deleteModel($model);
        }

        return $this->updateModel($model, $item, $id);
    }

    /**
     * Attempt to delete a geonames data by the given id and return true if success.
     */
    protected function delete(int $id): bool
    {
        $model = $this->findModel($id);

        if (! $model) {
            return false;
        }

        return $this->deleteModel($model);
    }

    /**
     * Insert the item into database.
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
     */
    protected function updateModel(Model $model, array $data, int $id): bool
    {
        echo "Updating model {$model->getMorphClass()}: {$id}\n";

        return $model->update(
            $this->resolveValues($this->mapUpdateFields($data, $id))
        );
    }

    /**
     * Delete the given model from the database.
     *
     * @throws Exception
     */
    protected function deleteModel(Model $model): bool
    {
        echo "Deleting model {$model->getMorphClass()}: {$model->geoname_id}\n";

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
