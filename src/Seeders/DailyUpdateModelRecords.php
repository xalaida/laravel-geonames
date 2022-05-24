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
}
