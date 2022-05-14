<?php

namespace Nevadskiy\Geonames\Seeders\City;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Nevadskiy\Geonames\Definitions\FeatureCode;
use Nevadskiy\Geonames\Parsers\GeonamesParser;
use Nevadskiy\Geonames\Seeders\Country\CountrySeeder;
use Nevadskiy\Geonames\Seeders\Division\DivisionSeeder;
use Nevadskiy\Geonames\Support\Batch\Batch;

// TODO: delete files using trash class (add to trash files and clear afterwards)
// TODO: consider adding scanning DB table to use only that attributes
class CitySeeder
{
    /**
     * TODO: guess the default model name.
     * The continent model class.
     */
    protected static $model;

    /**
     * @var array
     */
    private $countries;

    /**
     * @var array
     */
    private $divisions;

    /**
     * Use the given city model class.
     */
    public static function useModel(string $model): void
    {
        static::$model = $model;
    }

    public static function getModel(): Model
    {
        // TODO: check if class exists and is a subclass of eloquent model

        return new static::$model;
    }

    /**
     * Run the continent seeder.
     */
    public function seed(): void
    {
        $this->load();

        $batch = new Batch(function (array $records){
            $this->query()->insert($records);
        }, 1000);

        foreach ($this->cities() as $division) {
            $batch->push($division);
        }

        $batch->commit();
    }

    public function truncate()
    {
        $this->query()->truncate();
    }

    private function query(): Builder
    {
        return static::getModel()->newQuery();
    }

    public function cities(): iterable
    {
        $path = '/var/www/html/storage/meta/geonames/allCountries.txt';
        $geonamesParser = app(GeonamesParser::class);

        foreach ($geonamesParser->each($path) as $record) {
            if ($this->shouldSeed($record)) {
                yield $this->map($record);
            }
        }
    }

    protected function load(): void
    {
        $this->loadCountries();
        $this->loadDivisions();
    }

    protected function loadCountries(): void
    {
        $this->countries = CountrySeeder::getModel()
            ->newQuery()
            ->pluck('id', 'code')
            ->all();
    }

    protected function loadDivisions(): void
    {
        $this->divisions = DivisionSeeder::getModel()
            ->newQuery()
            ->get(['id', 'country_id', 'code'])
            ->groupBy(['country_id', 'code'])
            ->toArray();
    }

    /**
     * Determine if the given record should be seeded.
     */
    protected function shouldSeed(array $record): bool
    {
        // TODO: add filter by population.
        // TODO: add possibility to use different feature codes.

        return collect($this->featureCodes())->contains($record['feature code']);
    }

    /**
     * Get the list of feature codes of a country.
     */
    protected function featureCodes(): array
    {
        return [
            FeatureCode::PPL,
            FeatureCode::PPLC,
            FeatureCode::PPLA,
            FeatureCode::PPLA2,
            FeatureCode::PPLA3,
            FeatureCode::PPLX,
            FeatureCode::PPLG,
        ];
    }

    /**
     * Map fields of the given record to the continent model attributes.
     */
    protected function map(array $record): array
    {
        // TODO: think about processing using model (allows using casts and mutators)
        // TODO: remap fields

        return [
            'name' => $record['asciiname'] ?: $record['name'],
            'country_id' => $this->getCountryId($record),
            'division_id' => $this->getDivisionId($record),
            'latitude' => $record['latitude'],
            'longitude' => $record['longitude'],
            'timezone_id' => $record['timezone'],
            'population' => $record['population'],
            'elevation' => $record['elevation'],
            'dem' => $record['dem'],
            'feature_code' => $record['feature code'],
            'geoname_id' => $record['geonameid'],

            // TODO: think about this timestamps
            'synced_at' => $record['modification date'],
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    /**
     * Get a country ID by the given record.
     */
    protected function getCountryId(array $record): string
    {
        return $this->countries[$record['country code']];
    }

    /**
     * Get a division ID by the given record.
     */
    protected function getDivisionId(array $record): ?string
    {
        return $this->divisions[$this->getCountryId($record)][$record['admin1 code']][0]['id'] ?? null;
    }
}
