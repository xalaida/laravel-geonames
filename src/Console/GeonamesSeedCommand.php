<?php

namespace Nevadskiy\Geonames\Console;

use Illuminate\Console\Command;
use Nevadskiy\Geonames\Seeders\City\CitySeeder;
use Nevadskiy\Geonames\Seeders\CityTranslationsSeeder;
use Nevadskiy\Geonames\Seeders\Continent\ContinentSeeder;
use Nevadskiy\Geonames\Seeders\ContinentTranslationsSeeder;
use Nevadskiy\Geonames\Seeders\Country\CountrySeeder;
use Nevadskiy\Geonames\Seeders\Division\DivisionSeeder;
use Nevadskiy\Geonames\Seeders\CountryTranslationsSeeder;
use Nevadskiy\Geonames\Seeders\DivisionTranslationsSeeder;

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
        // TODO: do not import locales: wkdt, post, link, ...

        $seeders = [
            resolve(ContinentSeeder::class),
            resolve(CountrySeeder::class),
            resolve(DivisionSeeder::class),
            resolve(CitySeeder::class),
            resolve(ContinentTranslationsSeeder::class),
            resolve(CountryTranslationsSeeder::class),
            resolve(DivisionTranslationsSeeder::class),
            resolve(CityTranslationsSeeder::class),
        ];

        if ($this->option('truncate')) {
            $this->truncate($seeders);
        }

        // TODO: resolve all seeders using DI tagging.
        // TODO: consider adding translations strategy
        // TODO: delete downloaded files using TrashDecorator (push into trash when seeder is completed) and clear it in the console command before finish.
        // TODO: build console logger and set it from here like this:
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
     * Truncate the seeders.
     */
    private function truncate(array $seeders): void
    {
        // TODO: add confirmation
        // TODO: add success message

        foreach (array_reverse($seeders) as $seeder) {
            $seeder->truncate();
        }
    }

    private function seed(array $seeders): void
    {
        foreach ($seeders as $seeder) {
            $seeder->seed();
        }
    }
}
