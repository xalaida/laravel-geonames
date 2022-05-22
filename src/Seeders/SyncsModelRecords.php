<?php

namespace Nevadskiy\Geonames\Seeders;

use Illuminate\Database\Eloquent\Builder;
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
        $count = $this->query()->count();
        $syncedAt = $this->query()->max(self::SYNCED_AT);

        $this->resetSyncedAt();

        $this->syncRecords($this->getMappedRecordsForSyncing());

        $created = $this->query()->count() - $count;

        $updated = $this->query()
            ->when($syncedAt, function (Builder $query) use ($syncedAt) {
                $query->whereDate(self::SYNCED_AT, '>', $syncedAt);
            })
            ->count();

        $deleted = $this->deleteUnsyncedModels();

        // TODO: log report.
        dump("Created: {$created}");
        dump("Updated: {$updated}");
        dump("Deleted: {$deleted}");
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
        $updatable = $this->getUpdatableAttributes($records->first());

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
}
