<?php

namespace Nevadskiy\Geonames\Console\Seed;

use Illuminate\Console\Command;
use Nevadskiy\Geonames\Seeders\City\CitySeeder;
use Nevadskiy\Geonames\Seeders\Continent\ContinentSeeder;
use Nevadskiy\Geonames\Seeders\Country\CountrySeeder;
use Nevadskiy\Geonames\Seeders\Division\DivisionSeeder;

class GeonamesSeedCommand extends Command
{
    /**
     * The name and signature of the console command.
     * TODO: add description to options.
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
        $seeders = [
            resolve(ContinentSeeder::class),
            resolve(CountrySeeder::class),
            resolve(DivisionSeeder::class),
            resolve(CitySeeder::class),
        ];

        if ($this->option('truncate')) {
            $this->truncate($seeders);
        }

        // TODO: build console logger and set it from here like this:
        /*
         * function handle(Parser $parser)
         * {
         *   $parser->setLogger($this->consoleLogger())
         *   $parser->setProgress($this->consoleProgress())
         * }
         */

        // TODO: resolve all seeders using DI tagging.
        // TODO: add truncate method (probably truncate each seeder in reverse order).

        // TODO: delete downloaded files using TrashDecorator (push into trash when seeder is completed) and clear it in the console command before finish.

        // $this->compare();

        $this->seed($seeders);

        // $this->seedCountriesFromCountryInfo();
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
