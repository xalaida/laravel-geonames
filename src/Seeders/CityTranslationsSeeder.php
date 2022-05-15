<?php

namespace Nevadskiy\Geonames\Seeders;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\LazyCollection;
use Nevadskiy\Geonames\Parsers\AlternateNameParser;
use Nevadskiy\Geonames\Seeders\City\CitySeeder;

class CityTranslationsSeeder
{
    /**
     * Run the continent seeder.
     */
    public function seed(): void
    {
        foreach ($this->translations()->chunk(500) as $translations) {
            $this->query()->insert($translations->all());
        }
    }

    /**
     * Truncate translations of cities.
     */
    public function truncate(): void
    {
        $this->query()->truncate();
    }

    /**
     * Get translation records to insert.
     */
    public function translations(): LazyCollection
    {
        return LazyCollection::make(function () {
            foreach ($this->records()->chunk(500) as $records) {
                $cities = $this->getCitiesForRecords($records);

                foreach ($records as $record) {
                    if (isset($cities[$record['geonameid']])) {
                        yield $this->map($record, $cities);
                    }
                }
            }
        });
    }

    /**
     * Get cities for the given translation records.
     */
    public function getCitiesForRecords(LazyCollection $records): array
    {
        return CitySeeder::getModel()
            ->newQuery()
            ->whereIn('geoname_id', $records->pluck('geonameid')->unique())
            ->pluck('id', 'geoname_id')
            ->toArray();
    }

    /**
     * Get a query of city translations.
     */
    private function query(): Builder
    {
        return DB::table('city_translations');
    }

    /**
     * Get translation records.
     */
    public function records(): LazyCollection
    {
        // $path = resolve(DownloadService::class)->downloadAlternateNames();
        $path = '/var/www/html/storage/meta/geonames/alternateNames.txt';

        $parser = app(AlternateNameParser::class);

        return LazyCollection::make(function () use ($parser, $path) {
            foreach ($parser->each($path) as $record) {
                yield $record;
            }
        });
    }

    /**
     * Map fields of the given record to the model attributes.
     * TODO: consider extracting into separate Mapper class.
     */
    protected function map(array $record, array $cities): array
    {
        // TODO: think about processing using model (allows using casts and mutators).

        return [
            'city_id' => $cities[$record['geonameid']],
            'name' => $record['alternate name'],
            'is_preferred' => $record['isPreferredName'],
            'is_short' => $record['isShortName'],
            'is_colloquial' => $record['isColloquial'],
            'is_historic' => $record['isHistoric'],
            'geoname_id' => $record['geonameid'],
            'locale' => $record['isolanguage'],
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
