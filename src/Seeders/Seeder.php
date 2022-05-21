<?php

namespace Nevadskiy\Geonames\Seeders;

interface Seeder
{
    /**
     * Seed the dataset into database.
     */
    public function seed(): void;

    /**
     * Perform a daily update of the database.
     */
    public function update(): void;

    /**
     * Sync database according to the dataset.
     */
    public function sync(): void;

    /**
     * Truncate database before seeding.
     */
    public function truncate(): void;
}
