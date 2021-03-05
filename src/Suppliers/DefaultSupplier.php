<?php

namespace Nevadskiy\Geonames\Suppliers;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Nevadskiy\Geonames\Models\Continent;

abstract class DefaultSupplier implements Supplier
{
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
}
