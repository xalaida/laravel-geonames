<?php

namespace Nevadskiy\Geonames\Seeders;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\LazyCollection;

/**
 * @mixin ModelSeeder
 */
trait DailyUpdateModelRecords
{
    /**
     * Get records for a daily update.
     */
    abstract protected function getRecordsForDailyUpdate(): iterable;

    /**
     * Update database using the dataset with daily modifications.
     */
    protected function dailyUpdate(): Report
    {
        return $this->withReport(function () {
            $this->withLoadedResources(function () {
                foreach ($this->getKeyedRecordsForDailyUpdated()->chunk(1000) as $chunk) {
                    $this->resetSyncedAtForRecordKeys($chunk->keys()->all());

                    $this->syncRecords($this->mapRecords($chunk));
                }
            });
        });
    }

    /**
     * Reset the "synced at" timestamp for given records.
     */
    protected function resetSyncedAtForRecordKeys(array $keys): void
    {
        $this->query()
            ->whereIn(self::SYNC_KEY, $keys)
            ->toBase()
            ->update([self::SYNCED_AT => null]);
    }

    /**
     * Get mapped records for a daily update.
     */
    protected function getKeyedRecordsForDailyUpdated(): LazyCollection
    {
        return $this->mapRecordKeys($this->getRecordsForDailyUpdate());
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
