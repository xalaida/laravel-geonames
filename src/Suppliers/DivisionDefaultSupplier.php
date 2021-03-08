<?php

namespace Nevadskiy\Geonames\Suppliers;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
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
     * Filter entities according to the given countries.
     *
     * @var array|string[]
     */
    private $filterCountries;

    /**
     * The countries collection.
     *
     * @var Collection
     */
    protected $countries;

    /**
     * Make a new seeder instance.
     */
    public function __construct(int $batchSize = 1000, array $filterCountries = ['*'])
    {
        parent::__construct($batchSize);

        $this->filterCountries = $filterCountries;
    }

    /**
     * @inheritDoc
     */
    public function init(): void
    {
        parent::init();
        $this->countries = $this->getCountries();
    }

    /**
     * @inheritDoc
     */
    protected function getModel(): Model
    {
        return resolve(Division::class);
    }

    /**
     * @inheritDoc
     */
    protected function shouldSupply(array $data, int $id): bool
    {
        return $data['feature class'] === self::FEATURE_CLASS
            && in_array($data['feature code'], self::FEATURE_CODES, true)
            && ($this->filterCountries === ['*'] || in_array($data['country code'], $this->filterCountries, true));
    }

    /**
     * @inheritDoc
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
     * @inheritDoc
     */
    protected function mapUpdateFields(array $data, int $id): array
    {
        return [
            'name' => $data['asciiname'] ?: $data['name'],
            'country_id' => function () use ($data) {
                return $this->countries[$data['country code']]->id;
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
