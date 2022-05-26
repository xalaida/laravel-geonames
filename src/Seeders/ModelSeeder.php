<?php

namespace Nevadskiy\Geonames\Seeders;

use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\LazyCollection;

/**
 * @TODO: add soft deletes to deleted methods.
 */
abstract class ModelSeeder implements Seeder
{
    use SyncsModelRecords;
    use DailyUpdateModelRecords;
    use DailyDeleteModelRecords;

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
        foreach ($this->getRecordsForSeeding()->chunk(1000) as $chunk) {
            $this->query()->insert($chunk->all());
        }
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
     * Truncate a table of the model.
     */
    public function truncate(): void
    {
        $this->query()->truncate();
    }

    /**
     * Perform a daily update of the database.
     */
    public function update(): void
    {
        $report = $this->dailyUpdate();
        $report->incrementDeleted($this->dailyDelete());

        // TODO: log report ($report->logUsing($this->logger))
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
     * Map the given dataset to keyed records.
     */
    protected function mapRecordKeys(iterable $records): LazyCollection
    {
        return new LazyCollection(function () use ($records) {
            foreach ($records as $record) {
                yield $this->mapKey($record) => $record;
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
     * Map the record key.
     */
    protected function mapKey(array $record): string
    {
        return $record['geonameid'];
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
        $report->incrementDeleted($this->deleteUnsyncedModels());

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
}
