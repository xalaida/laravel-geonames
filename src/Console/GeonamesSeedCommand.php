<?php

namespace Nevadskiy\Geonames\Console;

use Illuminate\Console\Command;

class GeonamesSeedCommand extends Command
{
    /**
     * The name and signature of the console command.
     * TODO: add description to options
     * TODO: rewrite keep files to clean files
     *
     * @var string
     */
    protected $signature = 'geonames:seed {--truncate} {--keep-files}';

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

        if ($this->option('truncate')) {
            $this->truncate($seeders);
        }

        // TODO: do not import locales: wkdt, post, link, ...
        // TODO: build console logger and set it from here like this:
        // TODO: add decorator for downloader that captures all downloaded files and allow the possibility to delete them after.

        /**
         * function handle(Parser $parser)
         * {
         *   $parser->setLogger($this->consoleLogger())
         *   $parser->setProgress($this->consoleProgress())
         * }
         */

        $this->seed($seeders);
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

    /**
     * Truncate tables using given seeders.
     */
    private function truncate(array $seeders): void
    {
        // TODO: add confirmation
        // TODO: add success message

        foreach (array_reverse($seeders) as $seeder) {
            $seeder->truncate();
        }
    }

    /**
     * Seed the dataset using given seeders.
     */
    private function seed(array $seeders): void
    {
        foreach ($seeders as $seeder) {
            $seeder->seed();
        }
    }
}
