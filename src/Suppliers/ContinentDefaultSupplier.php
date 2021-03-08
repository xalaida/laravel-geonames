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
    protected function getModel(): Model
    {
        return resolve(Continent::class);
    }

    /**
     * @inheritDoc
     */
    protected function shouldSupply(array $data, int $id): bool
    {
        return $data['feature class'] === self::FEATURE_CLASS
            && in_array($data['feature code'], self::FEATURE_CODES, true);
    }

    /**
     * @inheritDoc
     */
    protected function mapInsertFields(array $data, int $id): array
    {
        return array_merge($this->mapUpdateFields($data, $id), [
            'id' => Continent::generateId(),
            'geoname_id' => $id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * @inheritDoc
     */
    protected function mapUpdateFields(array $data, int $id): array
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
