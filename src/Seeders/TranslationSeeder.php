<?php

namespace Nevadskiy\Geonames\Seeders;

use Generator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\LazyCollection;
use Nevadskiy\Geonames\Parsers\AlternateNameDeletesParser;
use Nevadskiy\Geonames\Parsers\AlternateNameParser;
use Nevadskiy\Geonames\Services\DownloadService;

abstract class TranslationSeeder implements Seeder
{
    use Concerns\UpdatesTranslationRecordsDaily;
    use Concerns\DeletesTranslationRecordsDaily;

    /**
     * The column name of the sync key.
     *
     * @var string
     */
    protected const SYNC_KEY = 'alternate_name_id';

    /**
     * The column name of the synced flag.
     *
     * @var string
     */
    protected const IS_SYNCED = 'is_synced';

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
     * Get a model for which translations are stored.
     */
    abstract protected function baseModel(): Model;

    /**
     * @inheritdoc
     */
    public function seed(): void
    {
        foreach ($this->getRecordsForSeeding()->chunk(1000) as $chunk) {
            $this->query()->insert($chunk->all());
        }
    }

    /**
     * Get prepared translation records for seeding.
     */
    protected function getRecordsForSeeding(): LazyCollection
    {
        return new LazyCollection(function () {
            foreach ($this->getRecordsCollection()->chunk(1000) as $chunk) {
                $this->loadResourcesBeforeMapping($chunk);

                foreach ($this->prepareRecords($chunk) as $record) {
                    yield $record;
                }

                $this->unloadResourcesAfterMapping();
            }
        });
    }

    // TODO: probably remove.
    protected function getRecordsCollection(): LazyCollection
    {
        return new LazyCollection(function () {
            foreach ($this->getRecords() as $record) {
                yield $record;
            }
        });
    }

    protected function loadResourcesBeforeMapping(LazyCollection $records): void
    {
        //
    }

    protected function unloadResourcesAfterMapping(): void
    {
        //
    }

    /**
     * Prepare records for seeding.
     */
    protected function prepareRecords(iterable $records): LazyCollection
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
     * @inheritdoc
     */
    public function update(): void
    {
        $this->dailyUpdate();
        $this->dailyDelete();
    }

    /**
     * Truncate the table with translations of the seeder.
     */
    public function truncate(): void
    {
        $this->query()->truncate();
    }

    /**
     * @inheritdoc
     */
    protected function getDailyModifications(): Generator
    {
        $path = resolve(DownloadService::class)->downloadDailyAlternateNamesModifications();

        foreach (resolve(AlternateNameParser::class)->each($path) as $record) {
            yield $record;
        }
    }

    /**
     * @inheritdoc
     */
    protected function getDailyDeletes(): Generator
    {
        $path = resolve(DownloadService::class)->downloadDailyAlternateNamesDeletes();

        foreach (resolve(AlternateNameDeletesParser::class)->each($path) as $record) {
            yield $record;
        }
    }

    /**
     * Get a query of model translations.
     */
    protected function query(): Builder
    {
        return $this->baseModel()
            ->translations()
            ->getModel()
            ->newQuery();
    }

    /**
     * Get the source records.
     */
    protected function getRecords(): Generator
    {
        $path = resolve(DownloadService::class)->downloadAlternateNames();

        foreach (resolve(AlternateNameParser::class)->each($path) as $record) {
            yield $record;
        }
    }

    /**
     * Determine if the given record should be seeded.
     */
    protected function filter(array $record): bool
    {
        // TODO: think about importing fallback locale... (what if fallback locale is custom, not english)

        return $this->isSupportedLocale($record['isolanguage']);
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
        return array_merge([
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
        ], $this->mapRelation($record));
    }

    /**
     * Map the relation attributes of the record.
     */
    abstract protected function mapRelation(array $record): array;

    protected function getUpdatableAttributes(): array
    {
        return [
            'name',
            'is_preferred',
            'is_short',
            'is_colloquial',
            'is_historic',
            'locale',
            'updated_at',
        ];
    }
}
