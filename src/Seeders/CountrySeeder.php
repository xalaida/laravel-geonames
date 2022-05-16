<?php

namespace Nevadskiy\Geonames\Seeders;

use Illuminate\Support\LazyCollection;
use Nevadskiy\Geonames\Definitions\FeatureCode;
use Nevadskiy\Geonames\Parsers\CountryInfoParser;
use Nevadskiy\Geonames\Parsers\GeonamesParser;
use Nevadskiy\Geonames\Services\DownloadService;

class CountrySeeder extends ModelSeeder implements Seeder
{
    /**
     * The country info list.
     *
     * @var array
     */
    private $countryInfo = [];

    /**
     * The continent list.
     *
     * @var array
     */
    private $continents = [];

    /**
     * @inheritdoc
     */
    public function seed(): void
    {
        $this->load();

        foreach ($this->countries()->chunk(1000) as $countries) {
            $this->query()->insert($countries->all());
        }

        $this->unload();
    }

    /**
     * @inheritdoc
     */
    public function update(): void
    {
        // TODO: Implement update() method.
    }

    /**
     * @inheritdoc
     */
    public function sync(): void
    {
        // TODO: Implement sync() method.
    }

    /**
     * Get the country records.
     */
    private function countries(): LazyCollection
    {
        // TODO: refactor downloading by passing Downloader instance from constructor.
        $path = resolve(DownloadService::class)->downloadAllCountries();

        return LazyCollection::make(function () use ($path) {
            foreach (resolve(GeonamesParser::class)->each($path) as $record) {
                if ($this->filter($record)) {
                    yield $this->mapRecord($record);
                }
            }
        });
    }

    /**
     * Load resources.
     */
    protected function load(): void
    {
        $this->loadCountryInfo();
        $this->loadContinents();
    }

    /**
     * Unload resources.
     */
    protected function unload(): void
    {
        $this->countryInfo = [];
        $this->continents = [];
    }

    /**
     * Load the country info resources.
     */
    protected function loadCountryInfo(): void
    {
        // TODO: refactor downloading by passing Downloader instance from constructor.
        $path = resolve(DownloadService::class)->downloadCountryInfo();

        $this->countryInfo = collect(resolve(CountryInfoParser::class)->all($path))
            ->keyBy('geonameid')
            ->all();
    }

    /**
     * Load the continent resources.
     */
    protected function loadContinents(): void
    {
        $this->continents = ContinentSeeder::getModel()
            ->newQuery()
            ->get()
            ->pluck('id', 'code')
            ->all();
    }

    /**
     * Determine if the given record should be seeded.
     */
    protected function filter(array $record): bool
    {
        if (! isset($this->countryInfo[$record['geonameid']])) {
            return false;
        }

        return collect($this->featureCodes())->contains($record['feature code']);
    }

    /**
     * Get the list of feature codes of a country.
     *
     * TODO: add possibility to specify dynamically.
     */
    protected function featureCodes(): array
    {
        return [
            FeatureCode::PCLI,
            FeatureCode::PCLD,
            FeatureCode::TERR,
            FeatureCode::PCLIX,
            FeatureCode::PCLS,
            FeatureCode::PCLF,
            FeatureCode::PCL,
        ];
    }

    /**
     * @inheritdoc
     */
    protected function mapAttributes(array $record): array
    {
        $countryInfo = $this->countryInfo[$record['geonameid']];

        return [
            // TODO: remap fields...
            'code' => $countryInfo['ISO'],
            'iso' => $countryInfo['ISO3'],
            'iso_numeric' => $countryInfo['ISO-Numeric'],
            'name' => $countryInfo['Country'],
            'continent_id' => $this->continents[$countryInfo['Continent']],
            'capital' => $countryInfo['Capital'],
            'currency_code' => $countryInfo['CurrencyCode'],
            'currency_name' => $countryInfo['CurrencyName'],
            'tld' => $countryInfo['tld'],
            'phone_code' => $countryInfo['Phone'],
            'postal_code_format' => $countryInfo['Postal Code Format'],
            'postal_code_regex' => $countryInfo['Postal Code Regex'],
            'languages' => $countryInfo['Languages'],
            'neighbours' => $countryInfo['neighbours'],
            'area' => $countryInfo['Area(in sq km)'],
            'fips' => $countryInfo['fips'],

            'name_official' => $record['asciiname'] ?: $record['name'],
            'timezone_id' => $record['timezone'],
            'latitude' => $record['latitude'],
            'longitude' => $record['longitude'],
            'population' => $record['population'],
            'dem' => $record['dem'],
            'feature_code' => $record['feature code'],
            'geoname_id' => $record['geonameid'],

            'synced_at' => $record['modification date'],
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
