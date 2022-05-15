<?php

namespace Nevadskiy\Geonames\Seeders;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Nevadskiy\Geonames\Definitions\FeatureCode;
use Nevadskiy\Geonames\Parsers\GeonamesParser;
use Nevadskiy\Geonames\Support\Batch\Batch;

// TODO: add possibility to stack with nevadskiy/money package
// TODO: delete files using trash class (add to trash files and clear afterwards)
class DivisionSeeder
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
     * Use the given division model class.
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

        foreach ($this->divisions() as $division) {
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

    public function divisions(): iterable
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
    }

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
    protected function shouldSeed(array $record): bool
    {
        return $record['feature code'] === FeatureCode::ADM1;
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

            // TODO: think about this timestamps
            'synced_at' => $record['modification date'],
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
