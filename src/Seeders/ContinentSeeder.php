<?php

namespace Nevadskiy\Geonames\Seeders;

use Illuminate\Support\Carbon;
use Nevadskiy\Geonames\Definitions\FeatureCode;
use Nevadskiy\Geonames\GeonamesSource;
use Nevadskiy\Geonames\Services\ContinentCodeGenerator;

class ContinentSeeder extends ModelSeeder
{
    /**
     * The continent model class.
     *
     * @var string
     */
    protected static $model = 'App\\Models\\Geo\\Continent';

    /**
     * The continent code generator instance.
     *
     * @var ContinentCodeGenerator
     */
    protected $codeGenerator;

    /**
     * The allowed feature codes.
     *
     * @var array
     */
    protected $featureCodes = [
        FeatureCode::CONT,
    ];

    /**
     * Make a new seeder instance.
     */
    public function __construct(GeonamesSource $source, ContinentCodeGenerator $codeGenerator)
    {
        parent::__construct($source);
        $this->codeGenerator = $codeGenerator;
    }

    /**
     * Use the given continent model class.
     * @TODO consider moving to parent class
     */
    public static function useModel(string $model): void
    {
        static::$model = $model;
    }

    /**
     * Get the continent model class.
     */
    public static function model(): string
    {
        return static::$model;
    }

    /**
     * {@inheritdoc}
     */
    protected function filter(array $record): bool
    {
        return in_array($record['feature code'], $this->featureCodes, true);
    }

    /**
     * {@inheritdoc}
     */
    protected function mapAttributes(array $record): array
    {
        return [
            'name' => $record['name'],
            'code' => $this->codeGenerator->generate($record['name']),
            'latitude' => $record['latitude'],
            'longitude' => $record['longitude'],
            'timezone_id' => $record['timezone'],
            'population' => $record['population'],
            'dem' => $record['dem'],
            'feature_code' => $record['feature code'],
            'geoname_id' => $record['geonameid'],
            'created_at' => now(),
            'updated_at' => Carbon::createFromFormat('Y-m-d', $record['modification date'])->startOfDay(),
        ];
    }

    /**
     * @inheritdoc
     */
    protected function updatable(): array
    {
        return [
            'code',
            'name',
            'latitude',
            'longitude',
            'timezone_id',
            'population',
            'dem',
            'feature_code',
            'updated_at',
        ];
    }
}
