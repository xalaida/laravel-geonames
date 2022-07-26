<?php

namespace Nevadskiy\Geonames\Console;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

class GeonamesSeedCommand extends Command
{
    use Seeders;

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
    public function handle(): void
    {
        $seeders = $this->seeders();

        $this->truncate($seeders);

        $this->seed($seeders);

        $this->clean();
    }

    /**
     * Truncate tables using given seeders.
     *
     * TODO: add prod confirmation.
     */
    protected function truncate(array $seeders): void
    {
        if ($this->option('truncate')) {
            foreach (array_reverse($seeders) as $seeder) {
                $seeder->truncate();
            }
        }
    }

    /**
     * Seed the dataset using given seeders.
     */
    protected function seed(array $seeders): void
    {
        foreach ($seeders as $seeder) {
            $seeder->seed();
        }
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
