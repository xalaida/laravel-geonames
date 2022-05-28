<?php

namespace Nevadskiy\Geonames\Seeders;

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
    protected static $model = 'App\\Models\\Geo\\Division';

    /**
     * The allowed feature codes.
     *
     * @var array
     */
    protected $featureCodes = [];

    /**
     * The country list.
     *
     * @var array
     */
    private $countries = [];

    /**
     * Make a new seeder instance.
     */
    public function __construct()
    {
        $this->featureCodes = [
            FeatureCode::ADM1,
        ];
    }

    /**
     * Use the given division model class.
     */
    public static function useModel(string $model): void
    {
        static::$model = $model;
    }

    /**
     * Get the division model class.
     */
    public function model(): string
    {
        return static::$model;
    }

    /**
     * {@inheritdoc}
     * @TODO refactor with DI downloader and parser.
     */
    protected function getRecords(): iterable
    {
        $path = resolve(DownloadService::class)->downloadAllCountries();

        foreach (resolve(GeonamesParser::class)->each($path) as $record) {
            yield $record;
        }
    }

    /**
     * {@inheritdoc}
     * @TODO refactor with DI downloader and parser.
     */
    protected function getDailyModificationRecords(): iterable
    {
        $path = resolve(DownloadService::class)->downloadDailyModifications();

        foreach (resolve(GeonamesParser::class)->each($path) as $record) {
            yield $record;
        }
    }

    /**
     * {@inheritdoc}
     * @TODO refactor with DI downloader and parser.
     */
    protected function getDailyDeleteRecords(): iterable
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
     * {@inheritdoc}
     */
    protected function filter(array $record): bool
    {
        return in_array($record['feature code'], $this->featureCodes, true);
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
