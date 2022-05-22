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
            $records = $this->getMappedRecordsForDailyUpdated();

            $this->resetSyncedAtForRecords($records);

            $this->syncRecords($this->mapRecords($records));
        });
    }

    /**
     * Reset the "synced at" timestamp for given records.
     */
    protected function resetSyncedAtForRecords(LazyCollection $records): void
    {
        foreach ($records->chunk(1000) as $chunk) {
            $this->query()
                ->whereIn(self::SYNC_KEY, $chunk->keys()->all())
                ->toBase()
                ->update([self::SYNCED_AT => null]);
        }
    }

    /**
     * Get mapped records for a daily update.
     */
    protected function getMappedRecordsForDailyUpdated(): LazyCollection
    {
        return $this->mapRecordKeys($this->getRecordsForDailyUpdate());
    }

    /**
     * Get updatable attributes of the model.
     * TODO: fetch attributes from db table, not record.
     */
    protected function getUpdatableAttributes(array $record): array
    {
        $updatable = $this->updatable();

        if (! $this->isWildcardAttributes($updatable)) {
            return $updatable;
        }

        return collect(array_keys($record))
            ->diff(['id', self::SYNC_KEY, 'created_at'])
            ->concat([self::SYNCED_AT, 'updated_at'])
            ->unique()
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
}
