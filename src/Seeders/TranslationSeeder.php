<?php

namespace Nevadskiy\Geonames\Seeders;

use Generator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\LazyCollection;
use Nevadskiy\Geonames\Parsers\AlternateNameDeletesParser;
use Nevadskiy\Geonames\Parsers\AlternateNameParser;
use Nevadskiy\Geonames\Services\DownloadService;
use RuntimeException;

abstract class TranslationSeeder implements Seeder
{
    use HasLogger;

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
     * Indicates if a nullable locale is allowed.
     *
     * @var array
     */
    protected $nullableLocale = true;

    /**
     * The parent model list for which translations are stored.
     *
     * @var array
     */
    protected $parentModels = [];

    /**
     * Make a new seeder instance.
     */
    public function __construct()
    {
        $this->locales = config('geonames.translations.locales');
        $this->nullableLocale = config('geonames.translations.nullable_locale');
    }

    /**
     * Get a base model class for which translations are stored.
     */
    abstract public static function baseModel(): string;

    /**
     * Get the base model instance of the seeder.
     */
    protected function newBaseModel(): Model
    {
        $model = static::baseModel();

        if (! is_a($model, Model::class, true)) {
            throw new RuntimeException(sprintf('The seeder model %s must extend the base Eloquent model.', $model));
        }

        return new $model();
    }

    /**
     * Get a query of model translations.
     */
    protected function query(): Builder
    {
        return $this->newBaseModel()
            ->translations()
            ->getModel()
            ->newQuery();
    }

    /**
     * {@inheritdoc}
     */
    public function seed(): void
    {
        $this->getLogger()->info(sprintf('Start seeding records using %s.', get_class($this)));

        foreach ($this->getRecordsForSeeding()->chunk(1000) as $chunk) {
            $this->query()->insert($chunk->all());
        }

        $this->getLogger()->info(sprintf('Records have been seeded using %s.', get_class($this)));
    }

    /**
     * Get mapped translation records for seeding.
     */
    protected function getRecordsForSeeding(): LazyCollection
    {
        return new LazyCollection(function () {
            foreach ($this->getRecordsCollection()->chunk(1000) as $chunk) {
                $this->loadResourcesBeforeChunkMapping($chunk);

                foreach ($this->mapRecords($chunk) as $record) {
                    yield $record;
                }

                $this->unloadResourcesAfterChunkMapping($chunk);
            }
        });
    }

    /**
     * Get a collection of records.
     */
    protected function getRecordsCollection(): LazyCollection
    {
        return new LazyCollection(function () {
            foreach ($this->getRecords() as $record) {
                yield $record;
            }
        });
    }

    /**
     * Get the source records.
     *
     * @TODO use DI downloader.
     * @TODO use DI parser.
     */
    protected function getRecords(): Generator
    {
        $path = resolve(DownloadService::class)->downloadAlternateNames();

        foreach (resolve(AlternateNameParser::class)->each($path) as $record) {
            yield $record;
        }
    }

    /**
     * Load resources before record attributes mapping.
     */
    protected function loadResourcesBeforeChunkMapping(LazyCollection $records): void
    {
        $this->parentModels = $this->newBaseModel()
            ->newQuery()
            ->whereIn('geoname_id', $records->pluck('geonameid')->unique())
            ->pluck('id', 'geoname_id')
            ->toArray();
    }

    /**
     * Unload resources after record attributes mapping.
     */
    protected function unloadResourcesAfterChunkMapping(LazyCollection $records): void
    {
        $this->parentModels = [];
    }

    /**
     * Map records for seeding.
     */
    protected function mapRecords(iterable $records): iterable
    {
        foreach ($records as $record) {
            if ($this->filter($record)) {
                yield $this->map($record);
            }
        }
    }

    /**
     * Determine if the given record should be seeded.
     */
    protected function filter(array $record): bool
    {
        return isset($this->parentModels[$record['geonameid']])
            && $this->isSupportedLocale($record['isolanguage']);
    }

    /**
     * Determine if the given locale is supported.
     *
     * @TODO consider importing fallback locale... (what if fallback locale is custom, not english)
     */
    protected function isSupportedLocale(?string $locale): bool
    {
        if (is_null($locale)) {
            return $this->nullableLocale;
        }

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
    protected function mapRelation(array $record): array
    {
        return [
            $this->getTranslationForeignKeyName() => $this->parentModels[$record['geonameid']],
        ];
    }

    /**
     * Get a foreign key name of the translation model.
     */
    protected function getTranslationForeignKeyName(): string
    {
        return $this->newBaseModel()
            ->translations()
            ->getForeignKeyName();
    }

    /**
     * Sync translations according to the geonames dataset.
     */
    public function sync(): void
    {
        $this->getLogger()->info(sprintf('Start syncing records using %s.', get_class($this)));

        $this->resetSyncedModels();

        $updatable = $this->getUpdatableAttributes();

        foreach ($this->getRecordsForSeeding()->chunk(1000) as $chunk) {
            $this->query()->upsert($chunk->all(), [self::SYNC_KEY], $updatable);
        }

        $this->deleteUnsyncedModels();

        $this->getLogger()->info(sprintf('Records have been synced using %s.', get_class($this)));
    }

    /**
     * Get updatable attributes of the model.
     */
    protected function getUpdatableAttributes(): array
    {
        $updatable = $this->updatable();

        if (! $this->isWildcardAttributes($updatable)) {
            return $updatable;
        }

        return collect($this->getColumns())
            ->diff([
                $this->query()->getModel()->getKeyName(),
                self::SYNC_KEY,
                $this->query()->getModel()::CREATED_AT
            ])
            ->values()
            ->all();
    }

    /**
     * Determine if the given attributes is a wildcard.
     */
    protected function isWildcardAttributes(array $attributes): bool
    {
        return count($attributes) === 1 && $attributes[0] === '*';
    }

    /**
     * Get the updatable attributes of the model.
     */
    protected function updatable(): array
    {
        return ['*'];
    }

    /**
     * Get column list for the database model.
     */
    protected function getColumns(): array
    {
        return $this->query()
            ->getConnection()
            ->getSchemaBuilder()
            ->getColumnListing($this->query()->getModel()->getTable());
    }

    /**
     * Reset a "sync" state for database models.
     */
    protected function resetSyncedModels(int $chunk = 50000): void
    {
        while ($this->synced()->exists()) {
            $this->synced()
                ->toBase()
                ->limit($chunk)
                ->update([self::IS_SYNCED => false]);
        }
    }

    /**
     * Get a query instance of synced models.
     */
    protected function synced(): Builder
    {
        return $this->query()
            ->whereNotNull(self::SYNC_KEY)
            ->where(self::IS_SYNCED, true);
    }

    /**
     * Delete unsynced models from database and return its amount.
     */
    protected function deleteUnsyncedModels(): int
    {
        $deleted = 0;

        while ($this->unsynced()->exists()) {
            $deleted += $this->unsynced()->delete();
        }

        return $deleted;
    }

    /**
     * Get a query instance of unsynced models.
     */
    protected function unsynced(): Builder
    {
        return $this->query()
            ->whereNotNull(self::SYNC_KEY)
            ->where(self::IS_SYNCED, false);
    }

    /**
     * {@inheritdoc}
     */
    public function update(): void
    {
        $this->getLogger()->info(sprintf('Start updating records using %s.', get_class($this)));

        $this->performDailyUpdate();
        $this->performDailyDelete();

        $this->getLogger()->info(sprintf('Records have been updated using %s.', get_class($this)));
    }

    /**
     * Perform a daily update of the translation records.
     */
    protected function performDailyUpdate(): void
    {
        $updatable = $this->getUpdatableAttributes();

        foreach ($this->getRecordsForDailyUpdate()->chunk(1000) as $chunk) {
            $this->query()->upsert($chunk->all(), [self::SYNC_KEY], $updatable);
        }

        $this->deleteUnsyncedModels();
    }

    /**
     * Get mapped records for a daily update.
     */
    protected function getRecordsForDailyUpdate(): LazyCollection
    {
        return new LazyCollection(function () {
            foreach ($this->getDailyModificationsCollection()->chunk(1000) as $chunk) {
                $this->resetSyncedModelsByRecords($chunk);

                $this->loadResourcesBeforeChunkMapping($chunk);

                foreach ($this->mapRecords($chunk) as $record) {
                    yield $record;
                }

                $this->unloadResourcesAfterChunkMapping($chunk);
            }
        });
    }

    /**
     * Get collection of records for daily modifications.
     */
    protected function getDailyModificationsCollection(): LazyCollection
    {
        return new LazyCollection(function () {
            foreach ($this->getDailyModificationRecords() as $record) {
                yield $record;
            }
        });
    }

    /**
     * Get records with daily modifications.
     *
     * @TODO use DI downloader.
     * @TODO use DI parser.
     */
    protected function getDailyModificationRecords(): Generator
    {
        $path = resolve(DownloadService::class)->downloadDailyAlternateNamesModifications();

        foreach (resolve(AlternateNameParser::class)->each($path) as $record) {
            yield $record;
        }
    }

    /**
     * Reset a "sync" state of models by the given records.
     */
    protected function resetSyncedModelsByRecords(iterable $records): void
    {
        $this->query()
            ->whereIn(self::SYNC_KEY, $this->getSyncKeysByRecords($records))
            ->update([self::IS_SYNCED => false]);
    }

    /**
     * Get sync keys by the given records.
     */
    protected function getSyncKeysByRecords(iterable $records): Collection
    {
        return (new Collection($records))->map(function (array $record) {
            return $record['alternateNameId'];
        });
    }

    /**
     * Perform a daily delete of the translation records.
     */
    protected function performDailyDelete(): void
    {
        foreach ($this->getRecordsForDailyDelete()->chunk(1000) as $chunk) {
            $this->query()
                ->whereIn(self::SYNC_KEY, $this->getSyncKeysByRecords($chunk))
                ->delete();
        }
    }

    /**
     * Get records for a daily delete.
     */
    protected function getRecordsForDailyDelete(): LazyCollection
    {
        return new LazyCollection(function () {
            foreach ($this->getDailyDeleteRecords() as $record) {
                yield $record;
            }
        });
    }

    /**
     * Get records with daily deletes.
     *
     * @TODO use DI downloader.
     * @TODO use DI parser.
     */
    protected function getDailyDeleteRecords(): Generator
    {
        $path = resolve(DownloadService::class)->downloadDailyAlternateNamesDeletes();

        foreach (resolve(AlternateNameDeletesParser::class)->each($path) as $record) {
            yield $record;
        }
    }

    /**
     * Truncate the table with translations of the seeder.
     */
    public function truncate(): void
    {
        $this->query()->truncate();

        $this->getLogger()->info(sprintf('Table has been truncated using %s.', get_class($this)));
    }
}
