<?php

namespace Nevadskiy\Geonames\Seeders\Country;

use App\Models\Geo\Continent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Nevadskiy\Geonames\Definitions\FeatureClass;
use Nevadskiy\Geonames\Definitions\FeatureCode;
use Nevadskiy\Geonames\Parsers\GeonamesParser;
use Nevadskiy\Geonames\Support\Batch\Batch;
use Nevadskiy\Geonames\Support\Exporter\ArrayExporter;

// TODO: add possibility to stack with nevadskiy/money package
// TODO: delete files using trash class (add to trash files and clear afterwards)
class CountrySeeder
{
    /**
     * TODO: guess the default model name.
     * The continent model class.
     */
    protected static $model;

    /**
     * @var array
     */
    private $countryInfo;

    /**
     * @var array
     */
    private $continents;

    /**
     * Use the given continent model class.
     */
    public static function useModel(string $model): void
    {
        static::$model = $model;
    }

    private function getModel(): Model
    {
        // TODO: check if class exists and is a subclass of eloquent model

        return new static::$model;
    }

    /**
     * Run the continent seeder.
     */
    public function seed(): void
    {
        $this->load();

        $batch = new Batch(function (array $records){
            $this->query()->insert($records);
        }, 1000);

        foreach ($this->getMappedCountries() as $country) {
            $batch->push($country);
        }

        $batch->commit();
    }

    public function truncate()
    {
        $this->query()->truncate();
    }

    private function query(): Builder
    {
        return $this->getModel()->newQuery();
    }

    public function getMappedCountries(): iterable
    {
        $path = '/var/www/html/storage/meta/geonames/allCountries.txt';
        $geonamesParser = app(GeonamesParser::class);

        foreach ($geonamesParser->each($path) as $record) {
            if ($this->isCountry($record)) {
                yield $this->map($record);
            }
        }
    }

    protected function load(): void
    {
        $this->loadCountryInfo();
        $this->loadContinents();
    }

    protected function loadCountryInfo(): void
    {
        // TODO: download this files.

        $this->countryInfo = collect(require storage_path('meta/geonames/country_info.php'))
            ->keyBy('geonameid')
            ->all();
    }

    protected function loadContinents(): void
    {
        // TODO: resolve model dynamically.

        $this->continents = Continent::all()
            ->pluck('id', 'code')
            ->all();
    }

    /**
     * Determine if the given record is a continent record.
     */
    protected function isCountry(array $record): bool
    {
        if (! isset($this->countryInfo[$record['geonameid']])) {
            return false;
        }

        return collect($this->featureCodes())->contains($record['feature code']);
    }

    /**
     * Get the list of feature codes of a country.
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
     * Map fields of the given record to the continent model attributes.
     */
    protected function map(array $record): array
    {
        // TODO: think about processing using model (allows using casts and mutators)

        $countryInfo = $this->countryInfo[$record['geonameid']];

        return [
            // TODO: remap fields
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

            // TODO: think about this timestamps
            'synced_at' => $record['modification date'],
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
