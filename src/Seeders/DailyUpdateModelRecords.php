<?php

namespace Nevadskiy\Geonames\Seeders;

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
                $updatable = $this->getUpdatableAttributes();

                // TODO: rewrite to guaranteed seed by chunks. currently it can be filtered and only 1 or 2 record will be inserted
                // TODO: look into CityTranslationSeeder.
                foreach ($this->getKeyedRecordsForDailyUpdated()->chunk(1000) as $chunk) {
                    $this->resetSyncedAtByKeys($chunk->keys()->all());

                    $this->query()->upsert($this->mapRecords($chunk)->all(), [self::SYNC_KEY], $updatable);
                }
            });
        });
    }

    /**
     * Reset the "synced at" timestamp for given record keys.
     */
    protected function resetSyncedAtByKeys(array $keys): void
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
}
