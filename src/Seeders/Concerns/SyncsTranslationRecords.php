<?php

namespace Nevadskiy\Geonames\Seeders\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\LazyCollection;
use Nevadskiy\Geonames\Seeders\TranslationSeeder;

/**
 * @mixin TranslationSeeder
 */
trait SyncsTranslationRecords
{
    /**
     * Daily delete records.
     */
    abstract protected function getDailyDeletes(): iterable;

    /**
     * Get mapped records for translation syncing.
     */
    protected function getRecordsForSyncing(): LazyCollection
    {
        return $this->getMappedRecordsForSeeding();
    }

    /**
     * Sync translations according to the geonames dataset.
     */
    public function sync(): void
    {
        $this->resetSync();

        $updatable = $this->getUpdatableAttributes();

        foreach ($this->getRecordsForSyncing()->chunk(1000) as $chunk) {
            $this->query()->upsert($chunk->all(), [self::SYNC_KEY], $updatable);
        }

        $this->deleteUnsyncedModels();
    }

    /**
     * Reset a "sync" state of the database models.
     */
    protected function resetSync(int $chunk = 50000): void
    {
        while ($this->synced()->exists()) {
            $this->synced()
                ->toBase()
                ->limit($chunk)
                ->update([self::IS_SYNCED => false]);
        }
    }

    /**
     * Delete unsynced models from the database and return its amount.
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
     * Get a query instance of synced models.
     */
    protected function synced(): Builder
    {
        return $this->query()->where(self::IS_SYNCED, true);
    }

    /**
     * Get a query instance of unsynced models.
     */
    protected function unsynced(): Builder
    {
        return $this->query()->where(self::IS_SYNCED, false);
    }
}
