<?php

namespace Nevadskiy\Geonames\Seeders;

use Generator;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\LazyCollection;
use Nevadskiy\Geonames\Parsers\AlternateNameParser;
use Nevadskiy\Geonames\Services\DownloadService;

class CityTranslationsSeeder implements Seeder
{
    /**
     * The cities list.
     *
     * @var array
     */
    protected $cities = [];

    /**
     * {@inheritdoc}
     */
    public function seed(): void
    {
        foreach ($this->getMappedRecordsForSeeding()->chunk(1000) as $chunk) {
            $this->query()->insert($chunk->all());
        }
    }

    /**
     * {@inheritdoc}
     */
    public function sync(): void
    {
        // TODO: Implement sync() method.
    }

    /**
     * {@inheritdoc}
     */
    public function update(): void
    {
        // TODO: Implement update() method.
    }

    /**
     * Truncate translations of cities.
     */
    public function truncate(): void
    {
        $this->query()->truncate();
    }

    /**
     * Get a query of city translations.
     */
    protected function query(): HasMany
    {
        return CitySeeder::model()->translations();
    }

    /**
     * Get mapped records for translation seeding.
     */
    protected function getMappedRecordsForSeeding(): LazyCollection
    {
        return new LazyCollection(function () {
            foreach ($this->getRecordsForSeeding()->chunk(1000) as $chunk) {
                $this->loadResourcesBeforeMapping($chunk);

                foreach ($this->mapRecords($chunk) as $record) {
                    yield $record;
                }

                $this->unloadResourcesAfterMapping();
            }
        });
    }

    /**
     * Map the given dataset to records for seeding.
     */
    protected function mapRecords(iterable $records): LazyCollection
    {
        return new LazyCollection(function () use ($records) {
            foreach ($records as $record) {
                if ($this->filter($record)) {
                    yield $this->map($record);
                }
            }
        });
    }

    /**
     * Get records for translation seeding.
     */
    protected function getRecordsForSeeding(): LazyCollection
    {
        return new LazyCollection(function () {
            foreach ($this->records() as $record) {
                yield $record;
            }
        });
    }

    /**
     * Get the source records.
     */
    protected function records(): Generator
    {
        $path = resolve(DownloadService::class)->downloadAlternateNames();

        foreach (resolve(AlternateNameParser::class)->each($path) as $record) {
            yield $record;
        }
    }

    protected function loadResourcesBeforeMapping(LazyCollection $records): void
    {
        $this->cities = CitySeeder::model()
            ->newQuery()
            ->whereIn('geoname_id', $records->pluck('geonameid')->unique())
            ->pluck('id', 'geoname_id')
            ->toArray();
    }

    protected function unloadResourcesAfterMapping(): void
    {
        $this->cities = [];
    }

    /**
     * Determine if the given record should be seeded.
     */
    protected function filter(array $record): bool
    {
        // TODO: use translation settings from config file.

        return isset($this->cities[$record['geonameid']]);
    }

    /**
     * Map the given record to the model attributes.
     */
    protected function map(array $record): array
    {
        return $this->query()
            ->getModel()
            ->forceFill($this->mapAttributes($record))
            ->getAttributes();
    }

    /**
     * Map fields to the model attributes.
     */
    protected function mapAttributes(array $record): array
    {
        return [
            'city_id' => $this->cities[$record['geonameid']],
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
