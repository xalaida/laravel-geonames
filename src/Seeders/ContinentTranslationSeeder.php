<?php

namespace Nevadskiy\Geonames\Seeders;

use Generator;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\LazyCollection;
use Nevadskiy\Geonames\Parsers\AlternateNameParser;
use Nevadskiy\Geonames\Services\DownloadService;

class ContinentTranslationSeeder implements Seeder
{
    use LoadsMappingResources;

    /**
     * The continent list.
     *
     * @var array
     */
    protected $continents = [];

    /**
     * @inheritdoc
     */
    public function seed(): void
    {
        $this->withLoadedResources(function () {
            foreach ($this->getMappedRecordsForSeeding()->chunk(1000) as $chunk) {
                $this->query()->insert($chunk->all());
            }
        });
    }

    public function sync(): void
    {
        //
    }

    /**
     * Sync database according to the given records.
     */
    protected function syncRecords(LazyCollection $records): void
    {
        $updatable = $this->getUpdatableAttributes();

        foreach ($records->chunk(1000) as $chunk) {
            $this->query()->upsert($chunk->all(), ['alternate_name_id'], $updatable);
        }
    }

    protected function getUpdatableAttributes(): array
    {
        return [
            // 'id',
            // 'city_id',
            'name',
            'is_preferred',
            'is_short',
            'is_colloquial',
            'is_historic',
            'locale',
            // 'alternate_name_id',
            // 'created_at',
            'updated_at',
        ];
    }

    public function update(): void
    {
        // TODO: Implement update() method.
    }

    public function truncate(): void
    {
        $this->query()->truncate();
    }

    protected function query(): HasMany
    {
        return ContinentSeeder::model()->translations();
    }

    public function getMappedRecordsForSeeding(): LazyCollection
    {
        return new LazyCollection(function () {
            foreach ($this->getRecordsForSeeding() as $record) {
                if ($this->filter($record)) {
                    yield $this->map($record);
                }
            }
        });
    }

    protected function getRecordsForSeeding(): Generator
    {
        $path = resolve(DownloadService::class)->downloadAlternateNames();

        foreach (app(AlternateNameParser::class)->each($path) as $record) {
            yield $record;
        }
    }

    /**
     * @inheritdoc
     */
    protected function loadResourcesBeforeMapping(): void
    {
        $this->continents = ContinentSeeder::model()
            ->newQuery()
            ->pluck('id', 'geoname_id')
            ->all();
    }

    /**
     * @inheritdoc
     */
    protected function unloadResourcesAfterMapping(): void
    {
        $this->continents = [];
    }

    /**
     * Determine if the given record should be seeded.
     */
    protected function filter(array $record): bool
    {
        // TODO: use translation settings from config file.

        return isset($this->continents[$record['geonameid']]);
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
            'continent_id' => $this->continents[$record['geonameid']],
            'name' => $record['alternate name'],
            'is_preferred' => $record['isPreferredName'],
            'is_short' => $record['isShortName'],
            'is_colloquial' => $record['isColloquial'],
            'is_historic' => $record['isHistoric'],
            'locale' => $record['isolanguage'],
            'alternate_name_id' => $record['...'],
            'is_synced' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
