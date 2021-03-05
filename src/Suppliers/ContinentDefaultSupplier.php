<?php

namespace Nevadskiy\Geonames\Suppliers;

use Illuminate\Database\Eloquent\Model;
use Nevadskiy\Geonames\Models\Continent;

class ContinentDefaultSupplier extends DefaultSupplier implements ContinentSupplier
{
    /**
     * Feature class of a continent.
     */
    public const FEATURE_CLASS = 'L';

    /**
     * Feature codes of a continent.
     */
    public const FEATURE_CODES = ['CONT'];

    /**
     * @inheritDoc
     */
    protected function shouldSupply(array $data): bool
    {
        return $data['feature class'] === self::FEATURE_CLASS
            && in_array($data['feature code'], self::FEATURE_CODES, true);
    }

    /**
     * @inheritDoc
     */
    protected function performInsert(array $data, int $id): bool
    {
        Continent::query()->create($this->mapInsertFields($data, $id));

        return true;
    }

    /**
     * @inheritDoc
     */
    protected function findModel(int $id): ?Model
    {
        return Continent::query()
            ->where('geoname_id', $id)
            ->first();
    }

    /**
     * @inheritDoc
     */
    protected function updateModel(Model $model, array $data): bool
    {
        return $model->update($this->mapUpdateFields($data));
    }

    /**
     * @inheritDoc
     */
    protected function deleteModel(Model $model): bool
    {
        return $model->delete();
    }

    /**
     * Map insert fields for the continent model.
     *
     * @param array $data
     * @param int $id
     * @return array
     */
    private function mapInsertFields(array $data, int $id): array
    {
        return array_merge($this->mapUpdateFields($data), [
            'geoname_id' => $id,
        ]);
    }

    /**
     * Map update fields for the continent model.
     *
     * @param array $data
     * @return array
     */
    private function mapUpdateFields(array $data): array
    {
        return [
            'name' => $data['name'],
            'latitude' => $data['latitude'],
            'longitude' => $data['longitude'],
            'timezone_id' => $data['timezone'],
            'population' => $data['population'],
            'dem' => $data['dem'],
            'modified_at' => $data['modification date'],
        ];
    }
}
