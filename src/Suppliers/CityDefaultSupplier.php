<?php

namespace Nevadskiy\Geonames\Suppliers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Nevadskiy\Geonames\Definitions\FeatureCode;
use Nevadskiy\Geonames\Geonames;
use Nevadskiy\Geonames\Models\City;
use Nevadskiy\Geonames\Models\Country;
use Nevadskiy\Geonames\Models\Division;

class CityDefaultSupplier extends DefaultSupplier implements CitySupplier
{
    /**
     * The city feature class.
     */
    public const FEATURE_CLASS = 'P';

    /**
     * The geonames instance.
     *
     * @var Geonames
     */
    protected $geonames;

    /**
     * The available countries collection.
     *
     * @var Collection
     */
    protected $availableCountries;

    /**
     * The available divisions collection.
     *
     * @var Collection
     */
    protected $availableDivisions;

    /**
     * Make a new supplier instance.
     */
    public function __construct(Geonames $geonames)
    {
        $this->geonames = $geonames;
    }

    /**
     * {@inheritdoc}
     */
    public function init(): void
    {
        parent::init();

        if ($this->geonames->shouldSupplyCountries()) {
            $this->availableCountries = $this->getCountries();
        }

        if ($this->geonames->shouldSupplyDivisions()) {
            $this->availableDivisions = $this->getDivisions();
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function getModel(): Model
    {
        return $this->geonames->model('city');
    }

    /**
     * {@inheritdoc}
     */
    protected function shouldSupply(array $data, int $id): bool
    {
        return $data['feature class'] === self::FEATURE_CLASS
            && in_array($data['feature code'], FeatureCode::cities(), true)
            && $this->geonames->isPopulationAllowed($data['population'])
            && $this->geonames->isCountryAllowed($data['country code']);
    }

    /**
     * {@inheritdoc}
     */
    protected function mapInsertFields(array $data, int $id): array
    {
        return array_merge($this->mapUpdateFields($data, $id), [
            'id' => City::generateId(),
            'geoname_id' => $id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    protected function mapUpdateFields(array $data, int $id): array
    {
        return [
            'name' => $data['asciiname'] ?: $data['name'],
            'country_id' => function () use ($data) {
                return $this->getCountryId($data);
            },
            'division_id' => function () use ($data) {
                return $this->getDivisionId($data);
            },
            'latitude' => $data['latitude'],
            'longitude' => $data['longitude'],
            'timezone_id' => $data['timezone'],
            'population' => $data['population'],
            'elevation' => $data['elevation'],
            'dem' => $data['dem'],
            'feature_code' => $data['feature code'],
            'modified_at' => $data['modification date'],
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
     * Get a country ID by the given city data.
     */
    protected function getCountryId(array $data): string
    {
        return $this->availableCountries[$data['country code']]->id;
    }

    /**
     * Get a division ID by the given city data.
     *
     * @return string
     */
    protected function getDivisionId(array $data): ?string
    {
        return $this->availableDivisions[$this->getCountryId($data)][$data['admin1 code']][0]->id ?? null;
    }
}
