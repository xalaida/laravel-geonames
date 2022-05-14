<?php

namespace Nevadskiy\Geonames\Console\Seed;

use Illuminate\Console\Command;
use Nevadskiy\Geonames\Definitions\FeatureClass;
use Nevadskiy\Geonames\Definitions\FeatureCode;
use Nevadskiy\Geonames\Parsers\CountryInfoParser;
use Nevadskiy\Geonames\Parsers\GeonamesParser;
use Nevadskiy\Geonames\Seeders\Continent\ContinentSeeder;
use Nevadskiy\Geonames\Services\DownloadService;
use Nevadskiy\Geonames\Support\Exporter\ArrayExporter;

class GeonamesSeedCommand extends Command
{
    /**
     * The name and signature of the console command.
     * TODO: add description to options
     *
     * @var string
     */
    protected $signature = 'geonames:seed {--reset} {--keep-files}';

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
        // TODO: build console logger and set it from here like this:
        /**
         * function handle(Parser $parser)
         * {
         *   $parser->setLogger($this->consoleLogger())
         *   $parser->setProgress($this->consoleProgress())
         * }
         */

        app(ContinentSeeder::class)->run();

        // $this->seedCountriesFromCountryInfo();
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

        // TODO: simple countries
        foreach ($countryInfoParser->each($path) as $country) {
            dd($country);

            // TODO: seed simple country...
            // TODO: update simple country...
            // TODO: sync simple country...
        }
    }
}
