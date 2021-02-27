<?php

namespace Nevadskiy\Geonames\Seeders;

interface Seeder
{
    /**
     * Seed the given geonames item into the database.
     */
    public function seed(array $item, int $id): void;
}
