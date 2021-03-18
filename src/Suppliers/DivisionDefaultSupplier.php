<?php

namespace Nevadskiy\Geonames\Suppliers;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Nevadskiy\Geonames\Geonames;
use Nevadskiy\Geonames\Models\Country;
use Nevadskiy\Geonames\Models\Division;

class DivisionDefaultSupplier extends DefaultSupplier implements DivisionSupplier
{
    /**
     * The division feature class.
     */
    public const FEATURE_CLASS = 'A';

    /**
     * The division feature codes.
     */
    public const FEATURE_CODES = [
        'ADM1',
    ];

    /**
     * The geonames instance.
     *
     * @var Geonames
     */
    private $geonames;

    /**
     * The available countries collection.
     *
     * @var Collection
     */
    protected $availableCountries;

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
    protected function init(): void
    {
        parent::init();

        if ($this->geonames->shouldSupplyCountries()) {
            $this->availableCountries = $this->getCountries();
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function getModel(): Model
    {
        return resolve(Division::class);
    }

    /**
     * {@inheritdoc}
     */
    protected function shouldSupply(array $data, int $id): bool
    {
        return $data['feature class'] === self::FEATURE_CLASS
            && in_array($data['feature code'], self::FEATURE_CODES, true)
            && $this->geonames->isCountryAllowed($data['country code']);
    }

    /**
     * {@inheritdoc}
     */
    protected function mapInsertFields(array $data, int $id): array
    {
        return array_merge($this->mapUpdateFields($data, $id), [
            'id' => Division::generateId(),
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
                return $this->availableCountries[$data['country code']]->id;
            },
            'latitude' => $data['latitude'],
            'longitude' => $data['longitude'],
            'timezone_id' => $data['timezone'],
            'population' => $data['population'],
            'elevation' => $data['elevation'],
            'dem' => $data['dem'],
            'code' => $data['admin1 code'],
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
}
