<?php

namespace Nevadskiy\Geonames\Seeders;

use Illuminate\Support\LazyCollection;

/**
 * @mixin ModelSeeder
 */
trait MapsRecords
{
    /**
     * Map the given dataset to records for seeding.
     * TODO: probably use different methods for update/delete/sync/seed
     */
    protected function mapRecords(iterable $records): LazyCollection
    {
        return LazyCollection::make(function () use ($records) {
            $this->load();

            foreach ($records as $record) {
                if ($this->filter($record)) {
                    yield $this->map($record);
                }
            }

            $this->unload();
        });
    }

    /**
     * Determine if the given record should be seeded.
     */
    protected function filter(array $record): bool
    {
        return true;
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
     * Load resources before mapping.
     */
    protected function load(): void
    {
        //
    }

    /**
     * Unload resources after mapping.
     */
    protected function unload(): void
    {
        //
    }
}
