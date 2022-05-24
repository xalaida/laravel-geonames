<?php

namespace Nevadskiy\Geonames\Seeders;

use Illuminate\Database\Eloquent\Model;
use Nevadskiy\Geonames\Definitions\FeatureCode;
use Nevadskiy\Geonames\Parsers\GeonamesDeletesParser;
use Nevadskiy\Geonames\Parsers\GeonamesParser;
use Nevadskiy\Geonames\Services\DownloadService;

class DivisionSeeder extends ModelSeeder
{
    /**
     * The seeder division model class.
     *
     * @var string
     */
    protected static $model;

    /**
     * The country list.
     *
     * @var array
     */
    private $countries = [];

    /**
     * Use the given division model class.
     */
    public static function useModel(string $model): void
    {
        static::$model = $model;
    }

    /**
     * Get the division model instance.
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
    protected function loadResourcesBeforeMapping(): void
    {
        $this->countries = CountrySeeder::model()
            ->newQuery()
            ->get()
            ->pluck('id', 'code')
            ->all();
    }

    /**
     * {@inheritdoc}
     */
    protected function unloadResourcesAfterMapping(): void
    {
        $this->countries = [];
    }

    /**
     * @inheritdoc
     */
    protected function filter(array $record): bool
    {
        return $record['feature code'] === FeatureCode::ADM1;
    }

    /**
     * {@inheritdoc}
     */
    protected function mapAttributes(array $record): array
    {
        return [
            'name' => $record['asciiname'] ?: $record['name'],
            'country_id' => $this->countries[$record['country code']],
            'latitude' => $record['latitude'],
            'longitude' => $record['longitude'],
            'timezone_id' => $record['timezone'],
            'population' => $record['population'],
            'elevation' => $record['elevation'],
            'dem' => $record['dem'],
            'code' => $record['admin1 code'],
            'feature_code' => $record['feature code'],
            'geoname_id' => $record['geonameid'],
            'synced_at' => $record['modification date'],
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
