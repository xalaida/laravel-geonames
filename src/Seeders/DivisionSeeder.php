<?php

namespace Nevadskiy\Geonames\Seeders;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\LazyCollection;
use Nevadskiy\Geonames\Definitions\FeatureCode;
use Nevadskiy\Geonames\Parsers\GeonamesParser;
use Nevadskiy\Geonames\Services\DownloadService;

class DivisionSeeder extends ModelSeeder implements Seeder
{
    /**
     * The seeder model class.
     */
    protected static $model;

    /**
     * The country list.
     *
     * @var array
     */
    private $countries = [];

    /**
     * Use the given model class.
     */
    public static function useModel(string $model): void
    {
        self::$model = $model;
    }

    /**
     * Get the model class.
     */
    public static function getModel(): Model
    {
        // TODO: check if class exists and is a subclass of eloquent model

        return new self::$model;
    }

    /**
     * Run the continent seeder.
     */
    public function seed(): void
    {
        $this->load();

        foreach ($this->divisions()->chunk(1000) as $divisions) {
            $this->query()->insert($divisions->all());
        }

        $this->unload();
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
    public function sync(): void
    {
        // TODO: Implement sync() method.
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
     * Load resources.
     */
    protected function load(): void
    {
        $this->loadCountries();
    }

    /**
     * Unload resources.
     */
    protected function unload(): void
    {
        $this->countries = [];
    }

    /**
     * Load country resources.
     */
    protected function loadCountries(): void
    {
        $this->countries = CountrySeeder::getModel()
            ->newQuery()
            ->get()
            ->pluck('id', 'code')
            ->all();
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
