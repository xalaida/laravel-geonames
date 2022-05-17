<?php

namespace Nevadskiy\Geonames\Console;

use Illuminate\Console\Command;

class GeonamesSyncCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'geonames:sync';

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
    }

    /**
     * Sync the dataset using given seeders.
     */
    protected function sync(array $seeders): void
    {
        foreach ($seeders as $seeder) {
            $seeder->sync();
        }
    }

    /**
     * Get the seeders list.
     */
    protected function seeders(): array
    {
        return collect(config('geonames.seeders'))
            ->map(function ($seeder) {
                return resolve($seeder);
            })
            ->all();
    }
}
