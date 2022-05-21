<?php

namespace Nevadskiy\Geonames\Seeders;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\LazyCollection;
use Nevadskiy\Geonames\Definitions\FeatureCode;
use Nevadskiy\Geonames\Parsers\GeonamesDeletesParser;
use Nevadskiy\Geonames\Parsers\GeonamesParser;
use Nevadskiy\Geonames\Services\ContinentCodeGenerator;
use Nevadskiy\Geonames\Services\DownloadService;

class ContinentSeeder extends ModelSeeder
{
    /**
     * The continent model class.
     *
     * @var string
     */
    protected static $model;

    /**
     * The continent code generator instance.
     *
     * @var ContinentCodeGenerator
     */
    protected $codeGenerator;

    /**
     * Make a new seeder instance.
     */
    public function __construct(ContinentCodeGenerator $codeGenerator)
    {
        $this->codeGenerator = $codeGenerator;
    }

    /**
     * Use the given continent model class.
     */
    public static function useModel(string $model): void
    {
        static::$model = $model;
    }

    /**
     * Get the continent model instance.
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
    protected function records(): LazyCollection
    {
        return $this->recordsByPath(
            // TODO: refactor downloading by passing Downloader instance from constructor.
            resolve(DownloadService::class)->downloadAllCountries()
        );
    }

    /**
     * {@inheritdoc}
     */
    public function update(): void
    {
        $this->dailyUpdate();
        $this->dailyDelete();
    }

    /**
     * Update database using the dataset with daily modifications.
     */
    protected function dailyUpdate(): void
    {
        $this->loadingResources(function () {
            $this->performDailyUpdate();
        });
    }

    /**
     * Perform a daily update of database records.
     */
    protected function performDailyUpdate(): void
    {
        // TODO: refactor by reusing sync method.

        $updatable = [];

        foreach ($this->recordsForDailyUpdate()->chunk(1000) as $records) {
            $updatable = $updatable ?: $this->getUpdatableAttributes($records->first());
            $this->query()->upsert($records->all(), [self::SYNC_KEY], $updatable);
        }
    }

    /**
     * Get records for daily update.
     */
    protected function recordsForDailyUpdate(): LazyCollection
    {
        return $this->recordsByPath(
            // TODO: refactor downloading by passing Downloader instance from constructor.
            resolve(DownloadService::class)->downloadDailyModifications()
        );
    }

    /**
     * Get records from a file by the given path.
     */
    protected function recordsByPath(string $path): LazyCollection
    {
        // TODO: resolve resources here

        return LazyCollection::make(function () use ($path) {
            // TODO: refactor using DI parser.
            foreach (resolve(GeonamesParser::class)->each($path) as $record) {
                if ($this->filter($record)) {
                    yield $this->map($record);
                }
            }
        });
    }

    /**
     * Delete records from database using the dataset with daily deletes.
     */
    protected function dailyDelete(): void
    {
        foreach ($this->recordsForDailyDelete()->chunk(1000) as $records) {
            $this->query()
                ->whereIn('geoname_id', $records->pluck('geonameid')->all())
                ->delete();
        }
    }

    /**
     * Get the dataset with daily deletes.
     */
    protected function recordsForDailyDelete(): LazyCollection
    {
        $path = resolve(DownloadService::class)->downloadDailyDeletes();

        return LazyCollection::make(function () use ($path) {
            // TODO: refactor using DI parser.
            foreach (resolve(GeonamesDeletesParser::class)->each($path) as $record) {
                yield $record;
            }
        });
    }

    /**
     * Determine if the given record should be seeded.
     */
    protected function filter(array $record): bool
    {
        // TODO: add possibility to configure filter outside.
        return $record['feature code'] === FeatureCode::CONT;
    }

    /**
     * Map fields to the model attributes.
     */
    protected function mapAttributes(array $record): array
    {
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
