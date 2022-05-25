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
     * The locale list.
     *
     * @var array
     */
    protected $locales = ['*'];

    /**
     * Make a new seeder instance.
     */
    public function __construct()
    {
        $this->locales = config('geonames.translations.locales');
    }

    /**
     * @inheritdoc
     */
    public function seed(): void
    {
        foreach ($this->getMappedRecordsForSeeding()->chunk(1000) as $chunk) {
            $this->query()->insert($chunk->all());
        }
    }

    /**
     * @inheritdoc
     */
    public function sync(): void
    {
        $this->resetIsSynced();

        // TODO: finish this...
    }

    /**
     * Reset the synced status of the records.
     */
    protected function resetIsSynced(): void
    {
        while ($this->synced()->exists()) {
            $this->synced()
                ->toBase()
                ->limit(50000)
                ->update(['is_synced' => false]);
        }
    }

    protected function synced(): HasMany
    {
        return $this->query()->where('is_synced', true);
    }

    /**
     * @inheritdoc
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
        // TODO: think about importing fallback locale... (what if fallback locale is custom, not english)

        return isset($this->cities[$record['geonameid']])
            && $this->isSupportedLocale($record['isolanguage']);
    }

    /**
     * Determine if the given locale is supported.
     */
    protected function isSupportedLocale(?string $locale): bool
    {
        if ($this->isWildcardLocale()) {
            return true;
        }

        return in_array($locale, $this->locales, true);
    }

    /**
     * Determine if the locale list is a wildcard.
     */
    protected function isWildcardLocale(): bool
    {
        return count($this->locales) === 1 && $this->locales[0] === '*';
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
            'is_preferred' => $record['isPreferredName'] ?: false,
            'is_short' => $record['isShortName'] ?: false,
            'is_colloquial' => $record['isColloquial'] ?: false,
            'is_historic' => $record['isHistoric'] ?: false,
            'locale' => $record['isolanguage'],
            'alternate_name_id' => $record['alternateNameId'],
            'is_synced' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
