<?php

namespace Nevadskiy\Geonames\Console\Import;

use Illuminate\Console\Command;
use Nevadskiy\Geonames\Models\Continent;
use Nevadskiy\Geonames\Models\Country;

class CountriesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'geonames:import:countries';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import countries dataset into database.';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        foreach ($this->getCountriesList() as $country) {
            $this->saveCountry($country);
        }

        $this->info('All countries have been imported.');
    }

    /**
     * Get the countries list.
     *
     * @return array
     */
    private function getCountriesList(): array
    {
        return require __DIR__ . '/../../../resources/data/countries.php';
    }

    /**
     * Save the given country.
     *
     * @param array $country
     */
    private function saveCountry(array $country): void
    {
        // TODO: convert empty strings to null.
        Country::create([
            'iso' => $country['ISO'],
            'iso3' => $country['ISO3'],
            'iso_numeric' => $country['ISO-Numeric'],
            'fips' => $country['fips'],
            'name' => $country['Country'],
            'name_official' => $country['name'],
            'capital' => $country['Capital'],
            'area' => $country['Area(in sq km)'],
            'population' => $country['Population'],
            'latitude' => $country['latitude'],
            'longitude' => $country['longitude'],
            'continent_id' => Continent::firstWhere('code', $country['Continent'])->id, // TODO: refactor
            'tld' => $country['tld'],
            'dem' => $country['dem'],
            'currency_code' => $country['CurrencyCode'],
            'currency_name' => $country['CurrencyName'],
            'phone_code' => $country['Phone'],
            'postal_code_format' => $country['Postal Code Format'],
            'postal_code_regex' => $country['Postal Code Regex'],
            'languages' => $country['Languages'],
            'neighbours' => $country['neighbours'],
            'feature_code' => $country['feature code'],
            'geoname_id' => $country['geonameid'],
            'created_at' => $country['modification date'],
            'updated_at' => $country['modification date'],
        ]);
    }
}
