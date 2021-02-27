<?php

namespace Nevadskiy\Geonames\Seeders;

use Illuminate\Database\Eloquent\Collection;
use Nevadskiy\Geonames\Models\Continent;
use Nevadskiy\Geonames\Models\Country;

class CountryDefaultSeeder implements CountrySeeder
{
    /**
     * The continents collection.
     */
    protected Collection $continents;

    /**
     * Make a new seeder instance.
     */
    public function __construct()
    {
        $this->continents = $this->getContinents();
    }

    /**
     * @inheritDoc
     */
    public function seed(array $data, int $id): void
    {
        Country::query()->updateOrCreate(['geoname_id' => $id], [
            'code' => $data['ISO'],
            'iso' => $data['ISO3'],
            'iso_numeric' => $data['ISO-Numeric'],
            'name' => $data['Country'],
            'name_official' => $data['asciiname'] ?: $data['name'],
            'timezone_id' => $data['timezone'],
            'latitude' => $data['latitude'],
            'longitude' => $data['longitude'],
            'continent_id' => $this->continents[$data['Continent']]->id,
            'capital' => $data['Capital'],
            'currency_code' => $data['CurrencyCode'],
            'currency_name' => $data['CurrencyName'],
            'tld' => $data['tld'],
            'phone_code' => $data['Phone'],
            'postal_code_format' => $data['Postal Code Format'],
            'postal_code_regex' => $data['Postal Code Regex'],
            'languages' => $data['Languages'],
            'neighbours' => $data['neighbours'],
            'area' => $data['Area(in sq km)'],
            'population' => $data['population'],
            'dem' => $data['dem'],
            'fips' => $data['fips'],
            'feature_code' => $data['feature code'],
            'geoname_id' => $id,
            'modified_at' => $data['modification date'],
        ]);
    }

    /**
     * Get continents collection grouped by code.
     */
    protected function getContinents(): Collection
    {
        return Continent::all()->keyBy('code');
    }
}
