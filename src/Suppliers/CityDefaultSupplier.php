<?php

namespace Nevadskiy\Geonames\Suppliers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Nevadskiy\Geonames\Models\City;
use Nevadskiy\Geonames\Models\Country;
use Nevadskiy\Geonames\Models\Division;
use Nevadskiy\Geonames\Support\Batch\Batch;

class CityDefaultSupplier extends DefaultSupplier implements CitySupplier
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
     * Indicates the minimal population for being seeded.
     *
     * @var int
     */
    private $minPopulation;

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
     * Insert cities batch to reduce queries amount to be performed.
     *
     * @var Batch
     */
    protected $insertBatch;

    /**
     * Make a new supplier instance.
     */
    public function __construct(int $batchSize = 1000, int $minPopulation = 0)
    {
        $this->minPopulation = $minPopulation;
        $this->insertBatch = $this->makeInsertBatch($batchSize);
    }

    /**
     * @inheritDoc
     */
    public function init(): void
    {
        parent::init();
        $this->countries = $this->getCountries();
        $this->divisions = $this->getDivisions();
    }

    /**
     * @inheritDoc
     */
    protected function shouldSupply(array $data, int $id): bool
    {
        return $data['feature class'] === self::FEATURE_CLASS
            && in_array($data['feature code'], self::FEATURE_CODES, true)
            && (int) $data['population'] >= $this->minPopulation;
    }

    /**
     * @inheritDoc
     */
    protected function performInsert(array $data, int $id): bool
    {
        $this->insertBatch->push(
            $this->resolveValues($this->mapInsetFields($data, $id))
        );

        return true;
    }

    /**
     * @inheritDoc
     */
    protected function findModel(int $id): ?Model
    {
        return City::query()
            ->where('geoname_id', $id)
            ->first();
    }

    /**
     * @inheritDoc
     */
    protected function updateModel(Model $model, array $data, int $id): bool
    {
        return $model->update(
            $this->resolveValues($this->mapUpdateFields($data))
        );
    }

    /**
     * @inheritDoc
     */
    protected function deleteModel(Model $model): bool
    {
        return $model->delete();
    }

    /**
     * Map fields for the city model.
     *
     * @param array $data
     * @param int $id
     * @return array
     */
    protected function mapInsetFields(array $data, int $id): array
    {
        return array_merge($this->mapUpdateFields($data), [
            'id' => City::generateId(),
            'geoname_id' => $id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Map fields for the city model.
     *
     * @param array $data
     * @return array
     */
    protected function mapUpdateFields(array $data): array
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
     * Make a batch instance for better inserting performance.
     *
     * @param int $batchSize
     * @return Batch
     */
    protected function makeInsertBatch(int $batchSize): Batch
    {
        return new Batch(static function (array $cities) {
            DB::table(City::TABLE)->insert($cities);
        }, $batchSize);
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
     *
     * @param array $data
     * @return string
     */
    protected function getCountryId(array $data): string
    {
        return $this->countries[$data['country code']]->id;
    }

    /**
     * Get a division ID by the given city data.
     *
     * @param array $data
     * @return string
     */
    protected function getDivisionId(array $data): ?string
    {
        return $this->divisions[$this->getCountryId($data)][$data['admin1 code']][0]->id ?? null;
    }

    /**
     * @inheritDoc
     */
    protected function getTableName(): string
    {
        return City::TABLE;
    }
}
