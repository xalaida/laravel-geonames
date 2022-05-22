<?php

namespace Nevadskiy\Geonames\Seeders;

use Illuminate\Support\LazyCollection;

/**
 * @mixin ModelSeeder
 */
trait DailyUpdate
{
    /**
     * Get records for a daily update.
     */
    abstract protected function getRecordsForDailyUpdate(): iterable;

    /**
     * Update database using the dataset with daily modifications.
     */
    protected function dailyUpdate(): void
    {
        $records = $this->getMappedRecordsForDailyUpdated();

        $this->resetSyncedAtForRecords($records);

        $updatable = $this->getUpdatableAttributes($records->first());

        foreach ($this->mapRecords($records)->chunk(1000) as $chunk) {
            $this->query()->upsert($chunk->all(), [self::SYNC_KEY], $updatable);
        }

        $this->deleteUnsyncedModels();
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
        return LazyCollection::make(function () {
            foreach ($this->getRecordsForDailyUpdate() as $record) {
                yield $this->mapUpdateKey($record) => $record;
            }
        });
    }

    /**
     * Map a key of the record for update.
     */
    protected function mapUpdateKey(array $record): string
    {
        return $this->mapKey($record);
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
