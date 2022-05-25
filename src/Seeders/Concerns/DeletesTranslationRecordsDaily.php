<?php

namespace Nevadskiy\Geonames\Seeders\Concerns;

use Illuminate\Support\LazyCollection;
use Nevadskiy\Geonames\Seeders\TranslationSeeder;

/**
 * @mixin TranslationSeeder
 */
trait DeletesTranslationRecordsDaily
{
    /**
     * Daily delete records.
     */
    abstract protected function getDailyDeletes(): iterable;

    /**
     * Perform a daily delete of the translation records.
     */
    protected function dailyDelete(): void
    {
        foreach ($this->getRecordsForDailyDelete()->chunk(1000) as $chunk) {
            $this->deleteRecords($chunk);
        }
    }

    /**
     * Get a collection with records for daily delete.
     */
    protected function getRecordsForDailyDelete(): LazyCollection
    {
        return new LazyCollection(function () {
            foreach ($this->getDailyDeletes() as $record) {
                yield $record;
            }
        });
    }

    /**
     * Delete the database records.
     */
    protected function deleteRecords(LazyCollection $records): void
    {
        $this->deleteRecordsByKeys(
            $this->getSyncKeysByRecords($records)->all()
        );
    }

    /**
     * Delete database records by the given keys.
     */
    protected function deleteRecordsByKeys(array $keys): void
    {
        $this->query()
            ->whereIn(self::SYNC_KEY, $keys)
            ->delete();
    }
}
