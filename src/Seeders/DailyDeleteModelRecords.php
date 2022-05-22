<?php

namespace Nevadskiy\Geonames\Seeders;

use Illuminate\Support\LazyCollection;

/**
 * @mixin ModelSeeder
 */
trait DailyDeleteModelRecords
{
    /**
     * Get records for a daily delete.
     */
    abstract protected function getRecordsForDailyDelete(): iterable;

    /**
     * Delete records from database using the dataset of daily deletes.
     */
    protected function dailyDelete(): int
    {
        $deleted = 0;

        foreach ($this->getMappedRecordsForDailyDelete()->chunk(1000) as $chunk) {
            $deleted += $this->query()
                ->whereIn(self::SYNC_KEY, $chunk->keys()->all())
                ->delete();
        }

        return $deleted;
    }

    /**
     * Get mapped records for a daily delete.
     */
    protected function getMappedRecordsForDailyDelete(): LazyCollection
    {
        return $this->mapRecordKeys($this->getRecordsForDailyDelete());
    }
}
