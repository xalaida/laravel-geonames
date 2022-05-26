<?php

namespace Nevadskiy\Geonames\Seeders;

use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\LazyCollection;
use Nevadskiy\Geonames\Parsers\AlternateNameDeletesParser;
use Nevadskiy\Geonames\Services\DownloadService;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * @TODO: add soft deletes to deleted methods.
 * @TODO: add possibility to use custom delete scopes.
 */
abstract class ModelSeeder implements Seeder
{
    /**
     * The column name of the synced date.
     *
     * @var string
     */
    protected const SYNCED_AT = 'synced_at';

    /**
     * The column name of the sync key.
     *
     * @var string
     */
    protected const SYNC_KEY = 'geoname_id';

    /**
     * The logger instance.
     *
     * @var LoggerInterface|null
     */
    protected $logger;

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
     * Get a new model instance of the seeder.
     */
    abstract protected function newModel(): Model;

    /**
     * Get a query instance of the seeder's model.
     */
    protected function query(): Builder
    {
        return $this->newModel()->newQuery();
    }

    /**
     * Get the source records.
     */
    abstract protected function getRecords(): iterable;

    /**
     * Seed records into database.
     */
    public function seed(): void
    {
        $this->getLogger()->info(sprintf('Start seeding records using %s.', get_class($this)));

        $created = 0;

        foreach ($this->getRecordsForSeeding()->chunk(1000) as $chunk) {
            $this->query()->insert($chunk->all());

            $created += $chunk->count();
        }

        $this->getLogger()->info(sprintf(
            "Records have been seeded using %s\n".
            "Created: %s\n",
            get_class($this),
            number_format($created)
        ));
    }

    /**
     * Get mapped records for seeding.
     */
    protected function getRecordsForSeeding(): LazyCollection
    {
        return new LazyCollection(function () {
            $this->loadResourcesBeforeMapping();

            foreach ($this->getRecordsCollection()->chunk(1000) as $chunk) {
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
        return $this->newModel()
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
        $this->getLogger()->info(sprintf('Start syncing records using %s.', get_class($this)));

        $report = $this->withReport(function () {
            $this->resetSyncedModels();

            $updatable = $this->getUpdatableAttributes();

            foreach ($this->getRecordsForSeeding()->chunk(1000) as $chunk) {
                $this->query()->upsert($chunk->all(), [self::SYNC_KEY], $updatable);
            }
        });

        $report->incrementDeleted(
            $this->deleteUnsyncedModels()
        );

        $this->getLogger()->info(sprintf(
            "Records have been synced using %s\n".
            "Created: %s\n".
            "Updated: %s\n".
            "Deleted: %s\n",
            get_class($this),
            number_format($report->getCreated()),
            number_format($report->getUpdated()),
            number_format($report->getDeleted())
        ));
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
            ->diff(['id', self::SYNC_KEY, 'created_at'])
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
        return DB::connection()
            ->getSchemaBuilder()
            ->getColumnListing($this->newModel()->getTable());
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
                ->update([self::SYNCED_AT => null]);
        }
    }

    /**
     * Get a query for synced models.
     */
    protected function synced(): Builder
    {
        return $this->query()->whereNotNull(self::SYNCED_AT);
    }

    /**
     * Delete unsynced models from database and return its amount.
     *
     * @TODO: add possibility to prevent models from being deleted... (probably use extended query with some scopes)
     * @TODO: integrate with soft delete.
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
     * Get a query for unsynced records.
     */
    protected function unsynced(): Builder
    {
        return $this->query()->whereNull(self::SYNCED_AT);
    }

    /**
     * Perform a daily update of the database.
     */
    public function update(): void
    {
        $this->getLogger()->info(sprintf('Start updating records using %s.', get_class($this)));

        $report = $this->performDailyUpdate();

        $report->incrementDeleted(
            $this->performDailyDelete()
        );

        $this->getLogger()->info(sprintf(
            "Records have been updated using %s\n".
            "Created: %s\n".
            "Updated: %s\n".
            "Deleted: %s\n",
            get_class($this),
            number_format($report->getCreated()),
            number_format($report->getUpdated()),
            number_format($report->getDeleted())
        ));
    }

    /**
     * Update database using the dataset with daily modifications.
     */
    protected function performDailyUpdate(): Report
    {
        $report = $this->withReport(function () {
            $updatable = $this->getUpdatableAttributes();

            foreach ($this->getRecordsForDailyUpdate()->chunk(1000) as $chunk) {
                $this->query()->upsert($chunk->all(), [self::SYNC_KEY], $updatable);
            }
        });

        $report->incrementDeleted($this->deleteUnsyncedModels());

        return $report;
    }

    /**
     * Get mapped records for a daily update.
     */
    protected function getRecordsForDailyUpdate(): LazyCollection
    {
        return new LazyCollection(function () {
            $this->loadResourcesBeforeMapping();

            foreach ($this->getDailyModificationsCollection()->chunk(1000) as $chunk) {
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
    protected function resetSyncedModelsByRecords(iterable $records): void
    {
        $this->query()
            ->whereIn(self::SYNC_KEY, $this->getSyncKeysByRecords($records))
            ->update([self::SYNCED_AT => null]);
    }

    /**
     * Get sync keys by the given records.
     */
    protected function getSyncKeysByRecords(iterable $records): Collection
    {
        return (new Collection($records))->map(function (array $record) {
            return $record['geonameid'];
        });
    }

    /**
     * Execute a callback and create a sync report.
     */
    protected function withReport(callable $callback): Report
    {
        $report = new Report();

        $count = $this->query()->count();
        $syncedAt = $this->getPreviousSyncDate();

        $callback();

        $report->incrementCreated($this->query()->count() - $count);
        $report->incrementUpdated($this->getUpdateRecordsCountFrom($syncedAt));

        return $report;
    }

    /**
     * Get a previous "synced_at" date.
     */
    protected function getPreviousSyncDate(): ?DateTimeInterface
    {
        $syncedAt = $this->query()->max(self::SYNCED_AT);

        if (! $syncedAt) {
            return null;
        }

        return Carbon::parse($syncedAt);
    }

    /**
     * Get an updated records count from the given sync date.
     */
    protected function getUpdateRecordsCountFrom(?DateTimeInterface $syncDate): int
    {
        return $this->query()
            ->when($syncDate, function (Builder $query) use ($syncDate) {
                $query->whereDate(self::SYNCED_AT, '>', $syncDate);
            })
            ->count();
    }

    /**
     * Delete records from database using the dataset of daily deletes.
     */
    protected function performDailyDelete(): int
    {
        $deleted = 0;

        foreach ($this->getRecordsForDailyDelete()->chunk(1000) as $chunk) {
            $deleted += $this->query()
                ->whereIn(self::SYNC_KEY, $this->getSyncKeysByRecords($chunk))
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

        $this->getLogger()->info(sprintf('Table have been truncated using %s.', get_class($this)));
    }
}
