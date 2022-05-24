<?php

namespace Nevadskiy\Geonames\Seeders;

use Generator;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\LazyCollection;
use Nevadskiy\Geonames\Parsers\AlternateNameParser;
use Nevadskiy\Geonames\Services\DownloadService;

class CountryTranslationsSeeder implements Seeder
{
    use LoadsMappingResources;

    /**
     * The countries list.
     *
     * @var array
     */
    protected $countries;

    /**
     * {@inheritdoc}
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
        // TODO: Implement sync() method.
    }

    public function update(): void
    {
        // TODO: Implement update() method.
    }

    /**
     * {@inheritdoc}
     */
    public function truncate(): void
    {
        $this->query()->truncate();
    }

    protected function getMappedRecordsForSeeding(): LazyCollection
    {
        return new LazyCollection(function () {
            foreach ($this->getRecordsForSeeding() as $record) {
                if ($this->filter($record)) {
                    yield $this->map($record);
                }
            }
        });
    }

    private function getRecordsForSeeding(): Generator
    {
        $path = resolve(DownloadService::class)->downloadAlternateNames();

        // TODO: make parser return generator instance.
        foreach (resolve(AlternateNameParser::class)->each($path) as $record) {
            yield $record;
        }
    }

    protected function query(): HasMany
    {
        return CountrySeeder::model()->translations();
    }

    /**
     * {@inheritdoc}
     */
    protected function loadResourcesBeforeMapping(): void
    {
        $this->countries = CountrySeeder::model()
            ->newQuery()
            ->pluck('id', 'geoname_id')
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
     * Determine if the given record should be seeded.
     */
    protected function filter(array $record): bool
    {
        return isset($this->countries[$record['geonameid']]);
    }

    /**
     * Map fields of the given record to the model attributes.
     */
    protected function map(array $record): array
    {
        // TODO: think about processing using model (allows using casts and mutators)

        return [
            'country_id' => $this->countries[$record['geonameid']],
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
