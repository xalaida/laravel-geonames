<?php

namespace Nevadskiy\Geonames\Seeders;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\LazyCollection;
use Nevadskiy\Downloader\Downloader;
use Nevadskiy\Geonames\Reader\Reader;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * @TODO: add soft deletes to deleted methods.
 * @TODO: add possibility to use custom delete scopes.
 * @TODO: delete static::newModel call (this is not abstract function)
 * @TODO: rename methods that return collection (getCollection or something)
 * @TODO: add possibility to change chunk size
 */
abstract class BaseSeeder implements Seeder, LoggerAwareInterface
{
    use HasLogger;

    /**
     * The downloader instance.
     *
     * @var Downloader
     */
    protected $downloader;

    /**
     * The reader instance.
     *
     * @var Reader
     */
    protected $reader;

    /**
     * The logger instance.
     *
     * @var LoggerInterface|null
     */
    protected $logger;

    /**
     * The chunk size of the records.
     */
    protected $chunkSize = 1000;

    /**
     * Make a new seeder instance.
     */
    public function __construct(Downloader $downloader, Reader $reader)
    {
        $this->downloader = $downloader;
        $this->reader = $reader;
    }

    /**
     * Get a query instance of the seeder.
     */
    abstract protected function query(): Builder;

    /**
     * Get the sync key of the seeder.
     */
    abstract protected function getSyncKeyName(): string;

    /**
     * Get the source records.
     */
    abstract protected function getRecords(): iterable;

    /**
     * Set the logger instance.
     */
    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }

    /**
     * Get the logger instance.
     */
    public function getLogger(): LoggerInterface
    {
        return $this->logger ?: new NullLogger();
    }

    /**
     * Seed records into database.
     */
    public function seed(): void
    {
        $this->getLogger()->info(sprintf('Start seeding records using: %s', get_class($this)));

        foreach ($this->getRecordsForSeeding()->chunk($this->chunkSize) as $chunk) {
            $this->query()->insert($chunk->all());
        }

        $this->getLogger()->info(sprintf('Finish seeding records using: %s', get_class($this)));
    }

    /**
     * Get mapped records for seeding.
     */
    protected function getRecordsForSeeding(): LazyCollection
    {
        return new LazyCollection(function () {
            $this->loadResourcesBeforeMapping();

            foreach ($this->getRecordsCollection()->chunk($this->chunkSize) as $chunk) {
                $this->loadResourcesBeforeChunkMapping($chunk);

                foreach ($this->mapRecords($chunk) as $record) {
                    yield $record;
                }

                $this->unloadResourcesAfterChunkMapping($chunk);
            }

            $this->unloadResourcesAfterMapping();
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
     * Load resources before records mapping of records.
     */
    protected function loadResourcesBeforeMapping(): void
    {
        //
    }

    /**
     * Unload resources after mapping of records.
     */
    protected function unloadResourcesAfterMapping(): void
    {
        //
    }

    /**
     * Load resources before mapping of chunk records.
     */
    protected function loadResourcesBeforeChunkMapping(LazyCollection $records): void
    {
        //
    }

    /**
     * Unload resources after mapping of chunk records.
     */
    protected function unloadResourcesAfterChunkMapping(LazyCollection $records): void
    {
        //
    }

    /**
     * Map records for seeding.
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
     * Determine if the given record should be seeded.
     */
    protected function filter(array $record): bool
    {
        return true;
    }

    /**
     * Map the given record to the model attributes.
     */
    protected function map(array $record): array
    {
        return static::query()
            ->getModel()
            ->forceFill($this->mapAttributes($record))
            ->getAttributes();
    }

    /**
     * Map fields to the model attributes.
     */
    abstract protected function mapAttributes(array $record): array;

    /**
     * Sync database according to the dataset.
     */
    public function sync(): void
    {
        $this->getLogger()->info(sprintf('Start syncing records using: %s', get_class($this)));

        $this->resetSyncedModels();

        $updatable = $this->getUpdatableAttributes();

        foreach ($this->getRecordsForSeeding()->chunk($this->chunkSize) as $chunk) {
            $this->query()->upsert($chunk->all(), [$this->getSyncKeyName()], $updatable);
        }

        $this->deleteUnsyncedModels();

        $this->getLogger()->info(sprintf('Finish syncing records using: %s', get_class($this)));
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
                static::query()->getModel()->getKeyName(),
                $this->getSyncKeyName(),
                static::query()->getModel()::CREATED_AT
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
            ->getColumnListing(static::query()->getModel()->getTable());
    }

    /**
     * Reset a "sync" state for database models.
     */
    protected function resetSyncedModels(int $chunk = 50000): void
    {
        while ($this->synced()->exists()) {
            $this->synced()
                ->limit($chunk)
                ->update(['updated_at' => null]);
        }
    }

    /**
     * Get a query for synced models.
     */
    protected function synced(): Builder
    {
        return $this->query()
            ->whereNotNull($this->getSyncKeyName())
            ->whereNotNull('updated_at');
    }

    /**
     * Delete unsynced models from database and return its amount.
     *
     * @TODO: add possibility to prevent models from being deleted... (probably use extended query with some scopes)
     * @TODO: integrate with soft delete.
     */
    protected function deleteUnsyncedModels(int $chunk = 50000): void
    {
        while ($this->unsynced()->exists()) {
            $this->unsynced()
                ->limit($chunk)
                ->delete();
        }
    }

    /**
     * Get a query for unsynced records.
     */
    protected function unsynced(): Builder
    {
        return $this->query()
            ->whereNotNull($this->getSyncKeyName())
            ->whereNull('updated_at');
    }

    /**
     * Perform a daily update of the database.
     */
    public function dailyUpdate(): void
    {
        $this->getLogger()->info(sprintf('Start updating records using: %s', get_class($this)));

        $this->applyDailyModifications();
        $this->applyDailyDeletes();

        $this->getLogger()->info(sprintf('Finish updating records using: %s', get_class($this)));
    }

    /**
     * Update database using the dataset with daily modifications.
     */
    protected function applyDailyModifications(): void
    {
        $updatable = $this->getUpdatableAttributes();

        foreach ($this->getRecordsForDailyUpdate()->chunk($this->chunkSize) as $chunk) {
            $this->query()->upsert($chunk->all(), [$this->getSyncKeyName()], $updatable);
        }

        $this->deleteUnsyncedModels();
    }

    /**
     * Get mapped records for a daily update.
     */
    protected function getRecordsForDailyUpdate(): LazyCollection
    {
        return new LazyCollection(function () {
            $this->loadResourcesBeforeMapping();

            foreach ($this->getDailyModificationsCollection()->chunk($this->chunkSize) as $chunk) {
                $this->resetSyncedModelsByRecords($chunk);

                $this->loadResourcesBeforeChunkMapping($chunk);

                foreach ($this->mapRecords($chunk) as $record) {
                    yield $record;
                }

                $this->unloadResourcesAfterChunkMapping($chunk);
            }

            $this->unloadResourcesAfterMapping();
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
     */
    abstract protected function getDailyModificationRecords(): iterable;

    /**
     * Reset a "sync" state of models by the given records.
     */
    protected function resetSyncedModelsByRecords(LazyCollection $records): void
    {
        $this->query()
            ->whereIn($this->getSyncKeyName(), $this->getSyncKeysByRecords($records))
            ->update(['updated_at' => null]);
    }

    /**
     * Get sync keys by the given records.
     */
    protected function getSyncKeysByRecords(LazyCollection $records): LazyCollection
    {
        return $records->map(function (array $record) {
            return $this->getSyncKeyByRecord($record);
        });
    }

    /**
     * Get a sync key by the given record.
     */
    abstract protected function getSyncKeyByRecord(array $record): int;

    /**
     * Delete records from database using the dataset of daily deletes.
     */
    protected function applyDailyDeletes(): int
    {
        $deleted = 0;

        foreach ($this->getRecordsForDailyDelete()->chunk($this->chunkSize) as $chunk) {
            $deleted += $this->query()
                ->whereIn($this->getSyncKeyName(), $this->getSyncKeysByRecords($chunk))
                ->delete();
        }

        return $deleted;
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
     */
    abstract protected function getDailyDeleteRecords(): iterable;

    /**
     * Truncate a table of the model.
     */
    public function truncate(): void
    {
        $this->query()->truncate();

        $this->getLogger()->info(sprintf('Table has been truncated using %s', get_class($this)));
    }
}