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

        $updatable = $this->getUpdatableAttributes();

        foreach ($this->getMappedRecordsForSyncing()->chunk(1000) as $chunk) {
            $this->query()->upsert($chunk->all(), [self::SYNC_KEY], $updatable);
        }

        $this->deleteUnsyncedModels();
    }

    /**
     * Reset the synced status of the models.
     */
    protected function resetIsSynced(): void
    {
        while ($this->synced()->exists()) {
            $this->synced()
                ->toBase()
                ->limit(50000)
                ->update([self::IS_SYNCED => false]);
        }
    }

    /**
     * Delete not synced records and return its amount.
     * TODO: add possibility to prevent models from being deleted... (probably use extended query with some scopes)
     * TODO: integrate with soft delete.
     */
    protected function deleteUnsyncedModels(): int
    {
        $deleted = 0;

        while ($this->unsynced()->exists()) {
            $deleted += $this->unsynced()->delete();
        }

        return $deleted;
    }

    protected function synced(): Builder
    {
        return $this->query()->where(self::IS_SYNCED, true);
    }

    protected function unsynced(): Builder
    {
        return $this->query()->where(self::IS_SYNCED, false);
    }

    /**
     * @inheritdoc
     */
    public function update(): void
    {
        $this->dailyUpdate();
    }

    /**
     * Truncate the table with translations of the seeder.
     */
    public function truncate(): void
    {
        $this->query()->truncate();
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
     * Get a model for which translations are stored.
     */
    abstract protected function baseModel(): Model;

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
     * Get mapped records for translation syncing.
     */
    protected function getMappedRecordsForSyncing(): LazyCollection
    {
        return $this->getMappedRecordsForSeeding();
    }

    /**
     * Map the given dataset to records for seeding.
     * TODO: rename method.
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
        //
    }

    protected function unloadResourcesAfterMapping(): void
    {
        //
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
