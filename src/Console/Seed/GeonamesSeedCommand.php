<?php

namespace Nevadskiy\Geonames\Console\Seed;

use Illuminate\Console\Command;
use Nevadskiy\Geonames\Definitions\FeatureClass;
use Nevadskiy\Geonames\Definitions\FeatureCode;
use Nevadskiy\Geonames\Parsers\CountryInfoParser;
use Nevadskiy\Geonames\Parsers\GeonamesParser;
use Nevadskiy\Geonames\Seeders\Continent\ContinentSeeder;
use Nevadskiy\Geonames\Seeders\Country\CountrySeeder;
use Nevadskiy\Geonames\Services\DownloadService;
use Nevadskiy\Geonames\Support\Exporter\ArrayExporter;
use Symfony\Component\Intl\Countries;

class GeonamesSeedCommand extends Command
{
    /**
     * The name and signature of the console command.
     * TODO: add description to options
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
        ];

        if ($this->option('truncate')) {
            $this->truncate($seeders);
        }

        // TODO: build console logger and set it from here like this:
        /**
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

    private function compare(): void
    {
        // TODO: if country code presents in country info and feature code is pass condition

        $countriesByFeatureCodes = require storage_path('meta/geonames/countries_codes.php');

//        collect(Countries::getNames())
//            ->diffKeys(collect($countriesByFeatureCodes)->pluck('name_official', 'country_code'))
//            ->dd();

        collect($countriesByFeatureCodes)
//            ->pluck('name_official', 'country_code')
            ->mapToGroups(function ($country) {
                return [$country['country_code'] => $country['name_official']];
            })
            ->dd();

        dd(collect($countriesByFeatureCodes)->count());

        collect($countriesByFeatureCodes)
            ->pluck('name_official', 'country_code')
            ->diffKeys(Countries::getNames())
            ->dd();

        collect($countriesByFeatureCodes)
            ->mapToGroups(function (array $country) {
                return [$country['feature_code'] => $country['name_official']];
            })
            ->dd();
    }

    private function seedContinents(): void
    {
        // TODO: probably add seeders to the config (key with alias and class and use this alias from console command)
        // TODO: allow to publish models using stubs (it mostly should be overwritten)

        // $downloadService = app(DownloadService::class);
        // $path = $downloadService->downloadAllCountries();

        $path = '/var/www/html/storage/meta/geonames/allCountries.txt';

        //dd($path);

        // TODO: grab all countries list and compare to default...

        $geonamesParser = app(GeonamesParser::class);

        $continents = [];

        foreach ($geonamesParser->each($path) as $geoname) {
            if ($geoname['feature class'] === FeatureClass::L && $geoname['feature code'] === FeatureCode::CONT) {
                $continents[] = $geoname;
                $this->info("Added continent {$geoname['name']}");
            }
        }

        dd($continents);

        // Export continent to file with array.
        // (new ArrayExporter())->export($continents, storage_path('meta/geonames/continents.php'));
    }

    private function seedCountriesFromCountryInfo(): void
    {
        // TODO: use the next layers: seeder (main entity)

        // TODO: probably add seeders to the config (key with alias and class and use this alias from console command)
        // TODO: allow to publish models using stubs (it mostly should be overwritten)

        // TODO: grab all countries list and compare to default...

        $downloadService = app(DownloadService::class);
        // $path = $downloadService->downloadCountryInfo();

        $path = '/var/www/html/storage/meta/geonames/countryInfo.txt';

        $countryInfoParser = app(CountryInfoParser::class);

        $countries = [];

        foreach ($countryInfoParser->each($path) as $country) {
            $countries[] = $country;
        }

        (new ArrayExporter())->export($countries, storage_path('meta/geonames/country_info.php'));
    }
}
