<?php

namespace Nevadskiy\Geonames\Seeders;

use Illuminate\Database\Eloquent\Model;
use Nevadskiy\Geonames\Definitions\FeatureCode;
use Nevadskiy\Geonames\Parsers\GeonamesDeletesParser;
use Nevadskiy\Geonames\Parsers\GeonamesParser;
use Nevadskiy\Geonames\Services\DownloadService;

// TODO: consider adding scanning DB table to use only that attributes
// TODO: add possibility to specify updatable attributes separately...
class CitySeeder extends ModelSeeder
{
    /**
     * The city model class.
     *
     * @var string
     */
    protected static $model;

    /**
     * The country resources.
     *
     * @var array
     */
    private $countries;

    /**
     * The division resources.
     *
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

    /**
     * Get the city model instance.
     */
    public static function model(): Model
    {
        // TODO: check if class exists and is a subclass of eloquent model
        // TODO: consider guessing default model name (or skip it since the model should be published directly from stubs)

        return new static::$model();
    }

    /**
     * {@inheritdoc}
     */
    protected function newModel(): Model
    {
        return static::model();
    }

    /**
     * {@inheritdoc}
     */
    protected function getRecordsForSeeding(): iterable
    {
        $path = resolve(DownloadService::class)->downloadAllCountries();

        foreach (resolve(GeonamesParser::class)->each($path) as $record) {
            yield $record;
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function getRecordsForDailyUpdate(): iterable
    {
        $path = resolve(DownloadService::class)->downloadDailyModifications();

        foreach (resolve(GeonamesParser::class)->each($path) as $record) {
            yield $record;
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function getRecordsForDailyDelete(): iterable
    {
        $path = resolve(DownloadService::class)->downloadDailyDeletes();

        foreach (resolve(GeonamesDeletesParser::class)->each($path) as $record) {
            yield $record;
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function load(): void
    {
        $this->loadCountries();
        $this->loadDivisions();
    }

    /**
     * Load country resources.
     */
    protected function loadCountries(): void
    {
        $this->countries = CountrySeeder::model()
            ->newQuery()
            ->pluck('id', 'code')
            ->all();
    }

    /**
     * Load division resources.
     */
    protected function loadDivisions(): void
    {
        $this->divisions = DivisionSeeder::model()
            ->newQuery()
            ->get(['id', 'country_id', 'code'])
            ->groupBy(['country_id', 'code'])
            ->toArray();
    }

    /**
     * {@inheritdoc}
     */
    protected function unload(): void
    {
        $this->countries = [];
        $this->divisions = [];
    }

    /**
     * Determine if the given record should be seeded.
     */
    protected function filter(array $record): bool
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
     * Map the given record to the model attributes.
     */
    protected function mapAttributes(array $record): array
    {
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
