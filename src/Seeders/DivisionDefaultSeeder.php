<?php

namespace Nevadskiy\Geonames\Seeders;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Nevadskiy\Geonames\Models\Country;
use Nevadskiy\Geonames\Models\Division;
use Nevadskiy\Geonames\Support\Batch\Batch;

class DivisionDefaultSeeder implements DivisionSeeder
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
    public function seed(array $division, int $id): void
    {
        if ($this->shouldSeed($division)) {
            $this->batch->push($this->mapFields($division, $id));
        }
    }

    /**
     * Determine whether the given division data should be seeded into the database.
     */
    protected function shouldSeed(array $division): bool
    {
        return $division['feature class'] === self::FEATURE_CLASS
            && in_array($division['feature code'], self::FEATURE_CODES, true);
    }

    /**
     * Map fields for the division model.
     *
     * @param array $data
     * @param int $id
     * @return array
     */
    protected function mapFields(array $data, int $id): array
    {
        return [
            'id' => Division::generateId(),
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
            'geoname_id' => $id,
            'modified_at' => $data['modification date'],
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
     * Make a batch instance for better inserting performance.
     *
     * @param int $batchSize
     * @return Batch
     */
    protected function makeBatch(int $batchSize): Batch
    {
        return new Batch(static function (array $cities) {
            DB::table(Division::TABLE)->insert($cities);
        }, $batchSize);
    }
}
