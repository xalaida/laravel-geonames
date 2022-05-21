<?php

namespace Nevadskiy\Geonames\Seeders;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\LazyCollection;

// TODO: extract to SyncsModels trait.
// TODO: split into traits.
// TODO: add soft deletes to deleted methods.
// TODO: define different methods for loading resources for seed/update/delete/sync
// TODO: define different methods for mapping for seed/update/delete/sync
abstract class ModelSeeder implements Seeder
{
    use MapsRecords;

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
     * {@inheritdoc}
     */
    public function seed(): void
    {
        foreach ($this->mapRecords($this->getRecordsForSeeding())->chunk(1000) as $records) {
            $this->query()->insert($records->all());
        }
    }

    /**
     * Get records for seeding.
     */
    abstract protected function getRecordsForSeeding(): iterable;

    /**
     * {@inheritdoc}
     */
    public function sync(): void
    {
        $count = $this->query()->count();
        $syncedAt = $this->query()->max(self::SYNCED_AT);
        $this->resetSyncedAt();

        $this->performSync();

        $created = $this->query()->count() - $count;

        $updated = $this->query()
            ->when($syncedAt, function (Builder $query) use ($syncedAt) {
                $query->whereDate(self::SYNCED_AT, '>', $syncedAt);
            })
            ->count();

        $deleted = $this->deleteNotSyncedRecords();

        // TODO: log report.
        dump("Created: {$created}");
        dump("Updated: {$updated}");
        dump("Deleted: {$deleted}");
    }

    /**
     * Truncate a table of the model.
     */
    public function truncate(): void
    {
        $this->query()->truncate();
    }

    /**
     * Get a query instance of the seeder's model.
     */
    protected function query(): Builder
    {
        return $this->newModel()->newQuery();
    }

    /**
     * Get updatable attributes of the model.
     */
    protected function getUpdatableAttributes(array $record): array
    {
        $updatable = $this->updatable();

        if (! $this->isWildcardAttributes($updatable)) {
            return $updatable;
        }

        return collect(array_keys($record))
            ->diff(['id', self::SYNC_KEY, 'created_at'])
            ->concat([self::SYNCED_AT, 'updated_at'])
            ->unique()
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
     * Perform the sync process.
     */
    protected function performSync(): void
    {
        $this->performUpdate($this->mapRecords($this->getRecordsForSyncing()));
    }

    /**
     * Get records for syncing database.
     */
    protected function getRecordsForSyncing(): iterable
    {
        return $this->getRecordsForSeeding();
    }

    /**
     * Reset the synced at timestamp for all records before syncing.
     */
    protected function resetSyncedAt(): void
    {
        while ($this->query()->whereNotNull(self::SYNCED_AT)->exists()) {
            $this->query()
                ->toBase()
                ->limit(50000)
                ->update([self::SYNCED_AT => null]);
        }
    }

    /**
     * Delete not synced records and return its amount.
     * TODO: add possibility to prevent models from being deleted... (probably use extended query with some scopes)
     * TODO: integrate with soft delete.
     */
    protected function deleteNotSyncedRecords(): int
    {
        $deleted = 0;

        while ($this->query()->whereNull(self::SYNCED_AT)->exists()) {
            $deleted += $this->query()->whereNull(self::SYNCED_AT)->delete();
        }

        return $deleted;
    }

    /**
     * Perform a daily update of the database.
     */
    public function update(): void
    {
        $this->dailyUpdate();
        $this->dailyDelete();
    }

    /**
     * Update database using the dataset with daily modifications.
     */
    protected function dailyUpdate(): void
    {
        $this->performUpdate($this->mapRecords($this->getRecordsForDailyUpdate()));
    }

    /**
     * Get records for daily update.
     */
    abstract protected function getRecordsForDailyUpdate(): iterable;

    /**
     * Update database using the given dataset of records.
     */
    protected function performUpdate(LazyCollection $dataset): void
    {
        // TODO: cover case when a record passed filter during seed process but do not pass during update process.

        $updatable = [];

        foreach ($dataset->chunk(1000) as $records) {
            $updatable = $updatable ?: $this->getUpdatableAttributes($records->first());

            $this->query()->upsert($records->all(), [self::SYNC_KEY], $updatable);
        }
    }

    /**
     * Delete records from database using the dataset with daily deletes.
     */
    protected function dailyDelete(): void
    {
        $records = LazyCollection::make(function () {
            foreach ($this->getRecordsForDailyDelete() as $record) {
                yield $record;
            }
        });

        foreach ($records->chunk(1000) as $records) {
            // TODO: refactor using mapSyncKey or something like that...
            $this->query()
                ->whereIn(self::SYNC_KEY, $records->pluck('geonameid')->all())
                ->delete();
        }
    }

    /**
     * Get records for daily delete.
     */
    abstract protected function getRecordsForDailyDelete(): iterable;
}
