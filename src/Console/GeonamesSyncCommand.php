<?php

namespace Nevadskiy\Geonames\Console;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

class GeonamesSyncCommand extends Command
{
    use Seeders;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'geonames:sync {--clean : Whether the directory with geonames downloads should be cleaned}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync the database according to the geonames dataset.';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $this->sync($this->seeders());

        $this->clean();
    }

    /**
     * Sync database using given seeders.
     */
    protected function sync(array $seeders): void
    {
        foreach ($seeders as $seeder) {
            $seeder->sync();
        }
    }

    /**
     * Clean the geonames downloads directory.
     */
    private function clean(): void
    {
        if ($this->option('clean')) {
            (new Filesystem)->cleanDirectory(config('geonames.directory'));
        }
    }
}
