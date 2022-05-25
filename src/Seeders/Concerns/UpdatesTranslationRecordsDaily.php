<?php

namespace Nevadskiy\Geonames\Seeders\Concerns;

use Illuminate\Support\Collection;
use Illuminate\Support\LazyCollection;
use Nevadskiy\Geonames\Seeders\TranslationSeeder;

/**
 * @mixin TranslationSeeder
 */
trait UpdatesTranslationRecordsDaily
{
    /**
     * Daily modification records.
     */
    abstract protected function getDailyModifications(): iterable;

    /**
     * Perform a daily update of the translation records.
     */
    protected function dailyUpdate(): void
    {
        $updatable = $this->getUpdatableAttributes();

        foreach ($this->getRecordsForDailyUpdate()->chunk(1000) as $chunk) {
            $this->query()->upsert($chunk->all(), [self::SYNC_KEY], $updatable);
        }

        $this->deleteUnsyncedModels();
    }

    /**
     * Get mapped records with daily modifications.
     */
    protected function getRecordsForDailyUpdate(): LazyCollection
    {
        return new LazyCollection(function () {
            foreach ($this->getDailyModificationCollection()->chunk(1000) as $chunk) {
                $this->resetSyncForRecords($chunk);

                $this->loadResourcesBeforeMapping($chunk);

                foreach ($this->mapRecords($chunk) as $record) {
                    yield $record;
                }

                $this->unloadResourcesAfterMapping();
            }
        });
    }

    /**
     * Get collection of records for daily modifications.
     */
    protected function getDailyModificationCollection(): LazyCollection
    {
        return new LazyCollection(function () {
            foreach ($this->getDailyModifications() as $record) {
                yield $record;
            }
        });
    }

    /**
     * Reset a "sync" state for the given records.
     */
    protected function resetSyncForRecords(iterable $records): void
    {
        $this->resetSyncByKeys(
            $this->getSyncKeysByRecords($records)->all()
        );
    }

    /**
     * Reset a "sync" state by the given model keys.
     */
    protected function resetSyncByKeys(array $keys): void
    {
        $this->query()
            ->whereIn(self::SYNC_KEY, $keys)
            ->update([self::IS_SYNCED => false]);
    }

    /**
     * Get sync keys by the given records.
     */
    protected function getSyncKeysByRecords(iterable $records): Collection
    {
        return (new Collection($records))->map(function (array $record) {
            return $this->getRecordSyncKey($record);
        });
    }

    /**
     * Get a sync key of the given record.
     */
    protected function getRecordSyncKey(array $record): int
    {
        return $record['alternateNameId'];
    }
}
