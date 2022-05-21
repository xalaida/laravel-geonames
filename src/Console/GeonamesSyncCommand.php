<?php

namespace Nevadskiy\Geonames\Console;

use Illuminate\Console\Command;

class GeonamesSyncCommand extends Command
{
    /**
     * The name and signature of the console command.
     * TODO: add description to options
     * TODO: rewrite keep files to clean files.
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
     * Sync database using given seeders.
     */
    protected function sync(array $seeders): void
    {
        foreach ($seeders as $seeder) {
            $seeder->sync();
        }
    }

    /**
     * Get the seeders list.
     * TODO: refactor using CompositeSeeder that resolves list automatically according to the config options.
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
