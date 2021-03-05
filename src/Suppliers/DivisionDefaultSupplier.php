<?php

namespace Nevadskiy\Geonames\Suppliers;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Nevadskiy\Geonames\Models\Country;
use Nevadskiy\Geonames\Models\Division;
use Nevadskiy\Geonames\Support\Batch\Batch;

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
     * The countries collection.
     *
     * @var Collection
     */
    protected $countries;

    /**
     * The batch for reducing amount of queries to be performed.
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
        $this->batch = $this->makeBatch($batchSize);
    }

    /**
     * @inheritDoc
     */
    protected function shouldSupply(array $data, int $id): bool
    {
        return $data['feature class'] === self::FEATURE_CLASS
            && in_array($data['feature code'], self::FEATURE_CODES, true);
    }

    /**
     * @inheritDoc
     */
    protected function performInsert(array $data, int $id): bool
    {
        $this->batch->push($this->mapInsertFields($data, $id));

        return true;
    }

    /**
     * @inheritDoc
     */
    protected function findModel(int $id): ?Model
    {
        return Division::query()
            ->where('geoname_id', $id)
            ->first();
    }

    /**
     * @inheritDoc
     */
    protected function updateModel(Model $model, array $data, int $id): bool
    {
        return $model->update($this->mapUpdateFields($data));
    }

    /**
     * @inheritDoc
     */
    protected function deleteModel(Model $model): bool
    {
        return $model->delete();
    }

    /**
     * Map fields for the division model.
     *
     * @param array $division
     * @param int $id
     * @return array
     */
    protected function mapInsertFields(array $division, int $id): array
    {
        return array_merge($this->mapUpdateFields($division), [
            'id' => Division::generateId(),
            'geoname_id' => $id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Map update fields for the division model.
     *
     * @param array $data
     * @return array
     */
    protected function mapUpdateFields(array $data): array
    {
        return [
            'name' => $data['asciiname'] ?: $data['name'],
            'country_id' => $this->countries[$data['country code']]->id,
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

    /**
     * Make a batch instance for better inserting performance.
     *
     * @param int $batchSize
     * @return Batch
     */
    protected function makeBatch(int $batchSize): Batch
    {
        return new Batch(static function (array $divisions) {
            DB::table(Division::TABLE)->insert($divisions);
        }, $batchSize);
    }
}
