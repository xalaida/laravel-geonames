<?php

namespace Nevadskiy\Geonames\Seeders;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\LazyCollection;

class CityTranslationSeeder extends TranslationSeeder
{
    /**
     * The cities list.
     *
     * @var array
     */
    protected $cities = [];

    /**
     * @inheritdoc
     */
    protected function baseModel(): Model
    {
        return CitySeeder::model();
    }

    /**
     * @inheritdoc
     */
    protected function mapRelation(array $record): array
    {
        return [
            'city_id' => $this->cities[$record['geonameid']],
        ];
    }

    protected function loadResourcesBeforeMapping(LazyCollection $records): void
    {
        $this->cities = CitySeeder::model()
            ->newQuery()
            ->whereIn('geoname_id', $records->pluck('geonameid')->unique())
            ->pluck('id', 'geoname_id')
            ->toArray();
    }

    protected function unloadResourcesAfterMapping(): void
    {
        $this->cities = [];
    }

    /**
     * Determine if the given record should be seeded.
     */
    protected function filter(array $record): bool
    {
        return isset($this->cities[$record['geonameid']]) && parent::filter($record);
    }
}
