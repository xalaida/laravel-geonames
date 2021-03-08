<?php

namespace Nevadskiy\Geonames\Suppliers;

use Illuminate\Database\Eloquent\Model;
use Nevadskiy\Geonames\Models\Continent;
use Nevadskiy\Geonames\Services\ContinentCodeGenerator;

class ContinentDefaultSupplier extends DefaultSupplier implements ContinentSupplier
{
    /**
     * Feature class of a continent.
     */
    public const FEATURE_CLASS = 'L';

    /**
     * Feature codes of a continent.
     */
    public const FEATURE_CODES = ['CONT'];

    /**
     * The code generator instance.
     *
     * @var ContinentCodeGenerator
     */
    private $codeGenerator;

    /**
     * ContinentDefaultSupplier constructor.
     */
    public function __construct(ContinentCodeGenerator $codeGenerator, int $batchSize = 1000)
    {
        parent::__construct($batchSize);
        $this->codeGenerator = $codeGenerator;
    }

    /**
     * @inheritDoc
     */
    protected function getModel(): Model
    {
        return resolve(Continent::class);
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
     * @inheritDoc
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
