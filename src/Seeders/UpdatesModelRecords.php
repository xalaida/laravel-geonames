<?php

namespace Nevadskiy\Geonames\Seeders;

use Illuminate\Support\LazyCollection;

/**
 * @mixin ModelSeeder
 */
trait UpdatesModelRecords
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
        // TODO: rework this.

        $this->performUpdate($this->mapRecords($this->getRecordsForDailyUpdate()));
    }

    /**
     * Update database using the given dataset of records.
     */
    protected function performUpdate(LazyCollection $dataset): void
    {
        // TODO: cover case when a record passed filter during seed process but do not pass during update process.

        $updatable = [];

        foreach ($dataset->chunk(1000) as $records) {
            // TODO: retrieve models by sync_key before filtering.
            // TODO: check if filter no longer pass, then delete record (delete using common delete method).

            $updatable = $updatable ?: $this->getUpdatableAttributes($records->first());

            $this->query()->upsert($records->all(), [self::SYNC_KEY], $updatable);
        }
    }

    /**
     * Get updatable attributes of the model.
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
