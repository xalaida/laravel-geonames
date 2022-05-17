<?php

namespace Nevadskiy\Geonames\Seeders;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

abstract class ModelSeeder implements Seeder
{
    /**
     * Truncate a table of the model.
     */
    public function truncate(): void
    {
        $this->query()->truncate();
    }

    /**
     * @inheritdoc
     */
    public function sync(): void
    {
        // TODO: add logging here...

        $count = $this->query()->count();
        $syncedAt = $this->query()->max('synced_at');

        $this->resetSyncedAt();

        $this->performSync();

        $created = $this->query()->count() - $count;
        $updated = $this->query()->whereDate('synced_at', '>', $syncedAt)->count();
        // TODO: add possibility to prevent models from being deleted... (probably use extended query with some scopes)
        // Delete can be danger here because empty file with destroy every record... also there is hard to delete every single record one be one... soft delete?
        $deleted = $this->query()->whereNull('synced_at')->delete();

        // TODO: log report.
        dump("Created: {$created}");
        dump("Updated: {$updated}");
        dump("Deleted: {$deleted}");
    }

    /**
     * Perform the sync process.
     */
    abstract protected function performSync(): void;

    protected function getUpdatableAttributes(array $record): array
    {
        $updatable = $this->updatable();

        if ($this->isWildcardAttributes($updatable)) {
            $updatable = array_keys($record);
        }

        return collect($updatable)
            ->diff(['id', 'geoname_id', 'created_at'])
            ->concat(['synced_at', 'updated_at'])
            ->unique()
            ->values()
            ->all();
    }

    protected function isWildcardAttributes(array $attributes): bool
    {
        return count($attributes) === 1 && $attributes[0] === '*';
    }

    protected function updatable(): array
    {
        return ['*'];
    }

    /**
     * @return void
     */
    protected function resetSyncedAt(): void
    {
        while ($this->query()->whereNotNull('synced_at')->exists()) {
            dump('nullifying...');

            $this->query()
                ->toBase()
                ->limit(50000)
                ->update(['synced_at' => null]);
        }
    }

    /**
     * Get a new model instance of the seeder.
     */
    abstract protected function newModel(): Model;

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
}
