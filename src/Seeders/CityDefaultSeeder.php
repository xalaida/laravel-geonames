<?php

namespace Nevadskiy\Geonames\Seeders;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Nevadskiy\Geonames\Models\City;
use Nevadskiy\Geonames\Models\Country;
use Nevadskiy\Geonames\Models\Division;
use Nevadskiy\Geonames\Support\Batch\Batch;

class CityDefaultSeeder implements CitySeeder
{
    /**
     * The city feature class.
     */
    public const FEATURE_CLASS = 'P';

    /**
     * The city feature codes.
     */
    public const FEATURE_CODES = ['PPL', 'PPLC', 'PPLA', 'PPLA2', 'PPLA3', 'PPLX', 'PPLG'];

    /**
     * The countries collection.
     *
     * @var Collection
     */
    protected $countries;

    /**
     * The divisions collection.
     *
     * @var Collection
     */
    protected $divisions;

    /**
     * Cities batch for reducing amount of queries to be performed.
     *
     * @var Batch
     */
    protected $batch;

    /**
     * Make a new seeder instance.
     */
    public function __construct(int $batchSize = 1000)
    {
        $this->countries = $this->getCountries();
        $this->divisions = $this->getDivisions();
        $this->batch = $this->makeBatch($batchSize);
    }

    /**
     * @inheritDoc
     */
    public function seed(array $data, int $id): void
    {
        if ($this->shouldSeed($data)) {
            $this->batch->push($this->mapFields($data, $id));
        }
    }

    /**
     * Determine whether the given city data should be seeded into the database.
     */
    protected function shouldSeed(array $city): bool
    {
        return $city['feature class'] === self::FEATURE_CLASS
            && in_array($city['feature code'], self::FEATURE_CODES, true)
            && (int) $city['population'] > 1000; // TODO: make population configurable
    }

    /**
     * Map fields for the city model.
     *
     * @param array $city
     * @param int $id
     * @return array
     */
    protected function mapFields(array $city, int $id): array
    {
        return [
            'id' => City::generateId(),
            'name' => $city['asciiname'] ?: $city['name'],
            'country_id' => $this->getCountryId($city),
            'division_id' => $this->getDivisionId($city),
            'latitude' => $city['latitude'],
            'longitude' => $city['longitude'],
            'timezone_id' => $city['timezone'],
            'population' => $city['population'],
            'elevation' => $city['elevation'],
            'dem' => $city['dem'],
            'feature_code' => $city['feature code'],
            'geoname_id' => $id,
            'modified_at' => $city['modification date'],
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    /**
     * Get countries collection grouped by code.
     */
    protected function getCountries(): Collection
    {
        return Country::all()->keyBy('code');
    }

    /**
     * Get divisions collection grouped by country and code.
     */
    protected function getDivisions(): Collection
    {
        return Division::all()->groupBy(['country_id', 'code']);
    }

    /**
     * Make a batch instance for better inserting performance.
     *
     * @param int $batchSize
     * @return Batch
     */
    protected function makeBatch(int $batchSize): Batch
    {
        return new Batch(static function (array $cities) {
            DB::table(City::TABLE)->insert($cities);
        }, $batchSize);
    }

    /**
     * Get a country ID by the given city data.
     *
     * @param array $city
     * @return string
     */
    protected function getCountryId(array $city): string
    {
        return $this->countries[$city['country code']]->id;
    }

    /**
     * Get a division ID by the given city data.
     *
     * @param array $city
     * @return string
     */
    protected function getDivisionId(array $city): ?string
    {
        return $this->divisions[$this->getCountryId($city)][$city['admin1 code']][0]->id ?? null;
    }
}
