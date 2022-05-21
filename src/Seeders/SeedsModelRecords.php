<?php

namespace Nevadskiy\Geonames\Seeders;

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
        foreach ($this->mapRecords($this->getRecordsForSeeding())->chunk(1000) as $records) {
            $this->query()->insert($records->all());
        }
    }
}
