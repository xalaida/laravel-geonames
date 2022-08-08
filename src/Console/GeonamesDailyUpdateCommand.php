<?php

namespace Nevadskiy\Geonames\Console;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Nevadskiy\Geonames\Seeders\CompositeSeeder;

class GeonamesDailyUpdateCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'geonames:daily-update {--keep-downloads : Do not clean directory with geonames downloads}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Perform a daily update of the database according to the geonames dataset.';

    /**
     * Execute the console command.
     */
    public function handle(CompositeSeeder $seeder): void
    {
        $seeder->dailyUpdate();

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
