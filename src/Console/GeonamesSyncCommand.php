<?php

namespace Nevadskiy\Geonames\Console;

use Illuminate\Console\Command;
use Nevadskiy\Geonames\Seeders\CitySeeder;
use Nevadskiy\Geonames\Seeders\CityTranslationsSeeder;
use Nevadskiy\Geonames\Seeders\ContinentSeeder;
use Nevadskiy\Geonames\Seeders\ContinentTranslationsSeeder;
use Nevadskiy\Geonames\Seeders\CountrySeeder;
use Nevadskiy\Geonames\Seeders\DivisionSeeder;
use Nevadskiy\Geonames\Seeders\CountryTranslationsSeeder;
use Nevadskiy\Geonames\Seeders\DivisionTranslationsSeeder;

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
        // TODO: do not import locales: wkdt, post, link, ...
        // TODO: configure donwloader to reuse existing file even when remote size is different

        $seeders = [
//            resolve(ContinentSeeder::class),
//            resolve(CountrySeeder::class),
//            resolve(DivisionSeeder::class),
            resolve(CitySeeder::class),
//            resolve(ContinentTranslationsSeeder::class),
//            resolve(CountryTranslationsSeeder::class),
//            resolve(DivisionTranslationsSeeder::class),
//            resolve(CityTranslationsSeeder::class),
        ];

        // $this->withProgressBar();

        $this->sync($seeders);
    }

    private function sync(array $seeders): void
    {
        foreach ($seeders as $seeder) {
            $seeder->sync();
        }
    }
}
