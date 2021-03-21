<?php

namespace Nevadskiy\Geonames\Suppliers;

use Illuminate\Database\Eloquent\Model;
use Nevadskiy\Geonames\Geonames;
use Nevadskiy\Geonames\Models\Continent;
use Nevadskiy\Geonames\Services\ContinentCodeGenerator;
use Nevadskiy\Geonames\Utils\FeatureCode;

class ContinentDefaultSupplier extends DefaultSupplier implements ContinentSupplier
{
    /**
     * Feature class of a continent.
     */
    public const FEATURE_CLASS = 'L';

    /**
     * The geonames instance.
     *
     * @var Geonames
     */
    protected $geonames;

    /**
     * The code generator instance.
     *
     * @var ContinentCodeGenerator
     */
    protected $codeGenerator;

    /**
     * Make a new supplier instance.
     */
    public function __construct(Geonames $geonames, ContinentCodeGenerator $codeGenerator)
    {
        $this->codeGenerator = $codeGenerator;
        $this->geonames = $geonames;
    }

    /**
     * {@inheritdoc}
     */
    protected function getModel(): Model
    {
        return $this->geonames->model('continent');
    }

    /**
     * {@inheritdoc}
     */
    protected function shouldSupply(array $data, int $id): bool
    {
        return $data['feature class'] === self::FEATURE_CLASS
            && $data['feature code'] === FeatureCode::CONT;
    }

    /**
     * {@inheritdoc}
     */
    protected function mapInsertFields(array $data, int $id): array
    {
        return array_merge($this->mapUpdateFields($data, $id), [
            'id' => Continent::generateId(),
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
            'name' => $data['name'],
            'code' => $this->codeGenerator->generate($data['name']),
            'latitude' => $data['latitude'],
            'longitude' => $data['longitude'],
            'timezone_id' => $data['timezone'],
            'population' => $data['population'],
            'dem' => $data['dem'],
            'feature_code' => $data['feature code'],
            'modified_at' => $data['modification date'],
        ];
    }
}
