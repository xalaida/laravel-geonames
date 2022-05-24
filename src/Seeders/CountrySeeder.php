<?php

namespace Nevadskiy\Geonames\Seeders;

use Illuminate\Database\Eloquent\Model;
use Nevadskiy\Geonames\Definitions\FeatureCode;
use Nevadskiy\Geonames\Parsers\CountryInfoParser;
use Nevadskiy\Geonames\Parsers\GeonamesDeletesParser;
use Nevadskiy\Geonames\Parsers\GeonamesParser;
use Nevadskiy\Geonames\Services\DownloadService;

class CountrySeeder extends ModelSeeder
{
    /**
     * The country model class.
     *
     * @var string
     */
    protected static $model;

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
     * Use the given country model class.
     */
    public static function useModel(string $model): void
    {
        static::$model = $model;
    }

    /**
     * Get the country model instance.
     */
    public static function model(): Model
    {
        // TODO: check if class exists and is a subclass of eloquent model
        // TODO: consider guessing default model name (or skip it since the model should be published directly from stubs)

        return new static::$model();
    }

    /**
     * {@inheritdoc}
     */
    protected function newModel(): Model
    {
        return static::model();
    }

    /**
     * {@inheritdoc}
     */
    protected function getRecordsForSeeding(): iterable
    {
        $path = resolve(DownloadService::class)->downloadAllCountries();

        foreach (resolve(GeonamesParser::class)->each($path) as $record) {
            yield $record;
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function getRecordsForDailyUpdate(): iterable
    {
        $path = resolve(DownloadService::class)->downloadDailyModifications();

        foreach (resolve(GeonamesParser::class)->each($path) as $record) {
            yield $record;
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function getRecordsForDailyDelete(): iterable
    {
        $path = resolve(DownloadService::class)->downloadDailyDeletes();

        foreach (resolve(GeonamesDeletesParser::class)->each($path) as $record) {
            yield $record;
        }
    }

    /**
     * @inheritdoc
     */
    protected function loadResourcesBeforeMapping(): void
    {
        $this->loadCountryInfo();
        $this->loadContinents();
    }

    /**
     * @inheritdoc
     */
    protected function unloadResourcesAfterMapping(): void
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
        $this->continents = ContinentSeeder::model()
            ->newQuery()
            ->get()
            ->pluck('id', 'code')
            ->all();
    }

    /**
     * @inheritdoc
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
