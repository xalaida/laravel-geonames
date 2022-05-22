<?php

namespace Nevadskiy\Geonames\Seeders;

use Illuminate\Support\LazyCollection;

/**
 * @mixin ModelSeeder
 */
trait DailyDelete
{
    /**
     * Get records for a daily delete.
     */
    abstract protected function getRecordsForDailyDelete(): iterable;

    /**
     * Delete records from database using the dataset of daily deletes.
     */
    protected function dailyDelete(): void
    {
        foreach ($this->getMappedRecordsForDailyDelete()->chunk(1000) as $records) {
            $this->query()
                ->whereIn(self::SYNC_KEY, $records->keys()->all())
                ->delete();
        }
    }

    /**
     * Get mapped records for a daily delete.
     */
    protected function getMappedRecordsForDailyDelete(): LazyCollection
    {
        return LazyCollection::make(function () {
            foreach ($this->getRecordsForDailyDelete() as $record) {
                yield $this->mapDeleteKey($record) => $record;
            }
        });
    }

    /**
     * Map a key of the record for delete.
     */
    protected function mapDeleteKey(array $record): string
    {
        return $this->mapKey($record);
    }
}
