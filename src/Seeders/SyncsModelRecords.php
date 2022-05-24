<?php

namespace Nevadskiy\Geonames\Seeders;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\LazyCollection;

/**
 * @mixin ModelSeeder
 */
trait SyncsModelRecords
{
    /**
     * Get records for syncing database.
     */
    protected function getRecordsForSyncing(): iterable
    {
        return $this->getRecordsForSeeding();
    }

    /**
     * Sync database according to the dataset.
     */
    public function sync(): void
    {
        $report = $this->withReport(function () {
            $this->resetSyncedAt();

            $this->withLoadedResources(function () {
                $this->syncRecords($this->getMappedRecordsForSyncing());
            });
        });

        dump("Created: {$report->getCreated()}");
        dump("Updated: {$report->getUpdated()}");
        dump("Deleted: {$report->getDeleted()}");
    }

    /**
     * Get mapped records for syncing.
     */
    protected function getMappedRecordsForSyncing(): LazyCollection
    {
        return $this->mapRecords($this->getRecordsForSyncing());
    }

    /**
     * Sync database according to the given records.
     */
    protected function syncRecords(LazyCollection $records): void
    {
        $updatable = $this->getUpdatableAttributes();

        foreach ($records->chunk(1000) as $chunk) {
            $this->query()->upsert($chunk->all(), [self::SYNC_KEY], $updatable);
        }
    }

    /**
     * Reset the "synced at" timestamp for all records before syncing.
     */
    protected function resetSyncedAt(): void
    {
        while ($this->synced()->exists()) {
            $this->synced()
                ->toBase()
                ->limit(50000)
                ->update([self::SYNCED_AT => null]);
        }
    }

    /**
     * Get a query for synced records.
     */
    protected function synced(): Builder
    {
        return $this->query()->whereNotNull(self::SYNCED_AT);
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

    /**
     * Get a query for unsynced records.
     */
    protected function unsynced(): Builder
    {
        return $this->query()->whereNull(self::SYNCED_AT);
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
}
