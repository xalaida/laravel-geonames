<?php

namespace Nevadskiy\Geonames\Console;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

class GeonamesDailyUpdateCommand extends Command
{
    use Seeders;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'geonames:daily-update {--clean : Whether the directory with geonames downloads should be cleaned}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Perform a daily update of the database according to the geonames dataset.';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $this->update($this->seeders());

        $this->clean();
    }

    /**
     * Update database using given seeders.
     */
    protected function update(array $seeders): void
    {
        foreach ($seeders as $seeder) {
            $seeder->dailyUpdate();
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
