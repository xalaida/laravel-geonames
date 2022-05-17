<?php

namespace Nevadskiy\Geonames\Seeders;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\LazyCollection;
use Nevadskiy\Geonames\Definitions\FeatureCode;
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

        return new static::$model;
    }

    /**
     * @inheritdoc
     */
    public function seed(): void
    {
        $this->loadingResources(function () {
            foreach ($this->divisions()->chunk(1000) as $divisions) {
                $this->query()->insert($divisions->all());
            }
        });
    }

    /**
     * @inheritdoc
     */
    public function update(): void
    {
        // TODO: Implement update() method.
    }

    /**
     * @inheritdoc
     */
    protected function newModel(): Model
    {
        return static::model();
    }

    /**
     * @inheritdoc
     */
    protected function performSync(): void
    {
        $this->loadingResources(function () {
            $updatable = [];

            foreach ($this->divisions()->chunk(1000) as $divisions) {
                $updatable = $updatable ?: $this->getUpdatableAttributes($divisions->first());
                $this->query()->upsert($divisions->all(), [self::SYNC_KEY], $this->getUpdatableAttributes($divisions->first()));
            }
        });
    }

    /**
     * Get the division records for seeding.
     */
    public function divisions(): LazyCollection
    {
        $path = resolve(DownloadService::class)->downloadAllCountries();

        return LazyCollection::make(function () use ($path) {
            foreach (resolve(GeonamesParser::class)->each($path) as $record) {
                if ($this->filter($record)) {
                    yield $this->map($record);
                }
            }
        });
    }

    /**
     * Execute a callback when resources are loaded.
     */
    private function loadingResources(callable $callback): void
    {
        $this->load();

        $callback();

        $this->unload();
    }

    /**
     * Load resources.
     */
    protected function load(): void
    {
        $this->countries = CountrySeeder::model()
            ->newQuery()
            ->get()
            ->pluck('id', 'code')
            ->all();
    }

    /**
     * Unload resources.
     */
    protected function unload(): void
    {
        $this->countries = [];
    }

    /**
     * Determine if the given record should be seeded.
     */
    protected function filter(array $record): bool
    {
        return $record['feature code'] === FeatureCode::ADM1;
    }

    /**
     * @inheritdoc
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
