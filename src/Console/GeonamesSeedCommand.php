<?php

namespace Nevadskiy\Geonames\Console;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Nevadskiy\Geonames\Seeders\CompositeSeeder;

class GeonamesSeedCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'geonames:seed
                            {--truncate : Whether the table should be truncated before seeding}
                            {--keep-downloads : Do not clean the directory with geonames downloads}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Seed the geonames dataset into the database.';

    /**
     * Execute the console command.
     */
    public function handle(CompositeSeeder $seeder): void
    {
        // TODO: add prod confirmation.
        if ($this->option('truncate')) {
            $seeder->truncate();
        }

        $seeder->seed();

        $this->clean();
    }

    /**
     * Clean the geonames downloads directory.
     */
    protected function clean(): void
    {
        if (! $this->option('keep-downloads')) {
            (new Filesystem)->cleanDirectory(config('geonames.directory'));
        }
    }
}
