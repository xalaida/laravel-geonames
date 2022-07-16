<?php

namespace Nevadskiy\Geonames\Seeders;

use Illuminate\Support\Carbon;
use Nevadskiy\Geonames\Definitions\FeatureCode;
use Nevadskiy\Geonames\Reader\CountryInfoReader;
use Nevadskiy\Geonames\Services\DownloadService;

class CountrySeeder extends NextModelSeeder
{
    /**
     * The country model class.
     *
     * @var string
     */
    protected static $model = 'App\\Models\\Geo\\Country';

    /**
     * The allowed feature codes.
     *
     * @var array
     */
    protected $featureCodes = [
        FeatureCode::PCLI,
        FeatureCode::PCLD,
        FeatureCode::TERR,
        FeatureCode::PCLIX,
        FeatureCode::PCLS,
        FeatureCode::PCLF,
        FeatureCode::PCL,
    ];

    /**
     * The country info list.
     *
     * @var array
     */
    protected $countryInfo = [];

    /**
     * The continent list.
     *
     * @var array
     */
    protected $continents = [];

    /**
     * Use the given country model class.
     */
    public static function useModel(string $model): void
    {
        static::$model = $model;
    }

    /**
     * Get the country model class.
     */
    public static function model(): string
    {
        return static::$model;
    }

    /**
     * {@inheritdoc}
     */
    protected function loadResourcesBeforeMapping(): void
    {
        $this->loadCountryInfo();
        $this->loadContinents();
    }

    /**
     * Load the country info resources.
     */
    protected function loadCountryInfo(): void
    {
        $this->countryInfo = collect($this->getCountryInfoRecords())
            ->keyBy('geoname_id')
            ->all();
    }

    /**
     * Get the country info records.
     */
    protected function getCountryInfoRecords(): iterable
    {
        return (new CountryInfoReader($this->reader))->getRecords(
            (new DownloadService($this->downloader))->downloadCountryInfo()
        );
    }

    /**
     * Load the continent resources.
     */
    protected function loadContinents(): void
    {
        $this->continents = ContinentSeeder::newModel()
            ->newQuery()
            ->pluck('id', 'code')
            ->all();
    }

    /**
     * {@inheritdoc}
     */
    protected function unloadResourcesAfterMapping(): void
    {
        $this->countryInfo = [];
        $this->continents = [];
    }

    /**
     * {@inheritdoc}
     */
    protected function filter(array $record): bool
    {
        if (! isset($this->countryInfo[$record['geonameid']])) {
            return false;
        }

        return in_array($record['feature code'], $this->featureCodes, true);
    }

    /**
     * {@inheritdoc}
     */
    protected function mapAttributes(array $record): array
    {
        return array_merge($this->mapCountryInfoAttributes($record), [
            'name_official' => $record['asciiname'] ?: $record['name'],
            'latitude' => $record['latitude'],
            'longitude' => $record['longitude'],
            'timezone_id' => $record['timezone'],
            'population' => $record['population'],
            'elevation' => $record['elevation'],
            'dem' => $record['dem'],
            'feature_code' => $record['feature code'],
            'geoname_id' => $record['geonameid'],
            'created_at' => now(),
            'updated_at' => Carbon::createFromFormat('Y-m-d', $record['modification date']),
        ]);
    }

    /**
     * Map attributes of the country info record.
     */
    protected function mapCountryInfoAttributes(array $record): array
    {
        $countryInfo = $this->countryInfo[$record['geonameid']];

        return [
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
        ];
    }
}
