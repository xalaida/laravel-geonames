<?php

namespace Nevadskiy\Geonames\Seeders;

use Illuminate\Support\LazyCollection;

/**
 * @mixin ModelSeeder
 */
trait SeedsModelRecords
{
    /**
     * Get records for seeding.
     */
    abstract protected function getRecordsForSeeding(): iterable;

    /**
     * Seed the dataset into database.
     */
    public function seed(): void
    {
        $this->withLoadedResources(function () {
            foreach ($this->getMappedRecordsForSeeding()->chunk(1000) as $chunk) {
                $this->query()->insert($chunk->all());
            }
        });
    }

    /**
     * Get mapped records for seeding.
     */
    protected function getMappedRecordsForSeeding(): LazyCollection
    {
        return $this->mapRecords($this->getRecordsForSeeding());
    }
}
