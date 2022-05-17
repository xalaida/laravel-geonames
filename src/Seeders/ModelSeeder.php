<?php

namespace Nevadskiy\Geonames\Seeders;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\LazyCollection;

abstract class ModelSeeder implements Seeder
{
    /**
     * The column name of the synced date.
     *
     * @var string
     */
    protected const SYNCED_AT = 'synced_at';

    /**
     * The column name of the sync key.
     *
     * @var string
     */
    protected const SYNC_KEY = 'geoname_id';

    /**
     * Get records for seeding.
     */
    abstract protected function records(): LazyCollection;

    /**
     * Get a new model instance of the seeder.
     */
    abstract protected function newModel(): Model;

    /**
     * @inheritdoc
     */
    public function seed(): void
    {
        $this->loadingResources(function () {
            foreach ($this->records()->chunk(1000) as $records) {
                $this->query()->insert($records->all());
            }
        });
    }

    /**
     * @inheritdoc
     */
    public function sync(): void
    {
        $count = $this->query()->count();
        $syncedAt = $this->query()->max(self::SYNCED_AT);
        $this->resetSyncedAt();

        $this->performSync();

        $created = $this->query()->count() - $count;
        $updated = $this->query()->whereDate(self::SYNCED_AT, '>', $syncedAt)->count();
        $deleted = $this->deleteNotSyncedRecords();

        // TODO: log report.
        dump("Created: {$created}");
        dump("Updated: {$updated}");
        dump("Deleted: {$deleted}");
    }

    /**
     * Truncate a table of the model.
     */
    public function truncate(): void
    {
        $this->query()->truncate();
    }

    /**
     * Get a query instance of the seeder's model.
     */
    protected function query(): Builder
    {
        return $this->newModel()->newQuery();
    }

    /**
     * Map the given record to the model attributes.
     */
    protected function map(array $record): array
    {
        return $this->newModel()
            ->forceFill($this->mapAttributes($record))
            ->getAttributes();
    }

    /**
     * Map fields to the model attributes.
     */
    abstract protected function mapAttributes(array $record): array;

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

    /**
     * Perform the sync process.
     */
    protected function performSync(): void
    {
        $this->loadingResources(function () {
            $updatable = [];

            foreach ($this->records()->chunk(1000) as $records) {
                $updatable = $updatable ?: $this->getUpdatableAttributes($records->first());
                $this->query()->upsert($records->all(), [self::SYNC_KEY], $updatable);
            }
        });
    }

    /**
     * Reset the synced at timestamp for all records before syncing.
     */
    protected function resetSyncedAt(): void
    {
        while ($this->query()->whereNotNull(self::SYNCED_AT)->exists()) {
            $this->query()
                ->toBase()
                ->limit(50000)
                ->update([self::SYNCED_AT => null]);
        }
    }

    /**
     * Delete not synced records and return its amount.
     * TODO: add possibility to prevent models from being deleted... (probably use extended query with some scopes)
     * TODO: integrate with soft delete
     */
    protected function deleteNotSyncedRecords(): int
    {
        $deleted = 0;

        while ($this->query()->whereNull(self::SYNCED_AT)->exists()) {
            $deleted += $this->query()->whereNull(self::SYNCED_AT)->delete();
        }

        return $deleted;
    }

    /**
     * Execute a callback when resources are loaded.
     */
    private function loadingResources(callable $callback): void
    {
        $this->load();

        $callback();

        $this->unload();
    }

    /**
     * Load resources.
     */
    protected function load(): void
    {
        //
    }

    /**
     * Unload resources.
     */
    protected function unload(): void
    {
        //
    }
}
