<?php

namespace Nevadskiy\Geonames\Console\Generate;

use Illuminate\Console\Command;
use Nevadskiy\Geonames\Parsers\CountryInfoParser;
use Nevadskiy\Geonames\Parsers\GeonamesParser;

/**
 * TODO: find the easies way to run via cli
 */
class CountriesResourceCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'geonames:generate:countries';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import the countries dataset';

    /**
     * Execute the console command.
     */
    public function handle(CountryInfoParser $countryInfoParser, GeonamesParser $geonamesParser): void
    {
        $countries = [];

        $countries = $this->processCountryInfo($countryInfoParser, $countries);

        $countries = $this->processGeonames($geonamesParser, $countries);

        $this->exportCountriesList($countries);

        $this->info('Countries resource has been generated!');
    }

    /**
     * @param CountryInfoParser $countryInfoParser
     * @return array
     */
    private function processCountryInfo(CountryInfoParser $countryInfoParser, array $countries): array
    {
        foreach ($countryInfoParser->each() as $countryInfo) {
            $countries[$countryInfo['geonameid']] = $countryInfo;
        }

        return $countries;
    }

    /**
     * @param GeonamesParser $geonamesParser
     * @param array $countries
     * @return array
     */
    private function processGeonames(GeonamesParser $geonamesParser, array $countries): array
    {
        foreach ($geonamesParser->each() as $geoname) {
            if (isset($countries[$geoname['geonameid']])) {
                $countries[$geoname['geonameid']] = array_merge($countries[$geoname['geonameid']], $geoname);
            }
        }

        return $countries;
    }

    /**
     * @param array $countries
     */
    private function exportCountriesList(array $countries): void
    {
        file_put_contents(__DIR__ . '/../../../countries.php', '<?php return ' . var_export($countries, true) . ";\n");
    }
}
