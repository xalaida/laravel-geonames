<?php

namespace Nevadskiy\Geonames\Seeders;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Nevadskiy\Geonames\Parsers\GeonamesParser;
use Nevadskiy\Geonames\Services\ContinentCodeGenerator;
use Nevadskiy\Geonames\Definitions\FeatureCode;
use Nevadskiy\Geonames\Support\Batch\Batch;

class ContinentSeeder
{
    /**
     * TODO: guess the default model name.
     * The continent model class.
     */
    protected static $model;

    /**
     * The continent code generator instance.
     *
     * @var ContinentCodeGenerator
     */
    private $codeGenerator;

    /**
     * Use the given continent model class.
     */
    public static function useModel(string $model): void
    {
        static::$model = $model;
    }

    /**
     * Make a new seeder instance.
     */
    public function __construct(ContinentCodeGenerator $codeGenerator)
    {
        $this->codeGenerator = $codeGenerator;
    }

    public static function getModel(): Model
    {
        // TODO: check if class exists and is a subclass of eloquent model

        return new static::$model;
    }

    public function truncate(): void
    {
        $this->query()->truncate();
    }

    private function query(): Builder
    {
        return static::getModel()->newQuery();
    }

    /**
     * Run the continent seeder.
     */
    public function seed(): void
    {
        $batch = new Batch(function (array $records){
            $this->query()->insert($records);
        }, 1000);

        foreach ($this->getMappedContinents() as $continent) {
            $batch->push($continent);
        }

        $batch->commit();
    }

    public function getMappedContinents(): iterable
    {
        $path = '/var/www/html/storage/meta/geonames/allCountries.txt';
        $geonamesParser = app(GeonamesParser::class);

        foreach ($geonamesParser->each($path) as $record) {
            if ($this->isContinent($record)) {
                yield $this->map($record);
            }
        }
    }

    /**
     * Determine if the given record is a continent record.
     */
    protected function isContinent(array $record): bool
    {
        // TODO: probably remove feature classes at all (can be resolved only by feature code)
        return $record['feature code'] === FeatureCode::CONT;
    }

    /**
     * Map fields of the given record to the continent model attributes.
     */
    protected function map(array $record): array
    {
        // TODO: think about processing using model (allows using casts and mutators)

        return [
            'name' => $record['name'],
            'code' => $this->codeGenerator->generate($record['name']),
            'latitude' => $record['latitude'],
            'longitude' => $record['longitude'],
            'timezone_id' => $record['timezone'],
            'population' => $record['population'],
            'dem' => $record['dem'],
            'feature_code' => $record['feature code'],
            'geoname_id' => $record['geonameid'],
            'synced_at' => $record['modification date'],
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
