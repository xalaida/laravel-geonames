<?php

namespace Nevadskiy\Geonames\Seeders;

use Illuminate\Database\Eloquent\Builder;

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

        $deleted = $this->deleteDanglingModelsAfterSyncing();

        // TODO: log report.
        dump("Created: {$created}");
        dump("Updated: {$updated}");
        dump("Deleted: {$deleted}");
    }

    /**
     * Perform the sync process.
     */
    protected function performSync(): void
    {
        $updatable = [];

        foreach ($this->mapRecords($this->getRecordsForSyncing())->chunk(1000) as $records) {
            $updatable = $updatable ?: $this->getUpdatableAttributes($records->first());

            $this->query()->upsert($records->all(), [self::SYNC_KEY], $updatable);
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
    protected function deleteDanglingModelsAfterSyncing(): int
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
}
