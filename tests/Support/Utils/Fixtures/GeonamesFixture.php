<?php

namespace Nevadskiy\Geonames\Tests\Support\Utils\Fixtures;

use Illuminate\Foundation\Testing\WithFaker;
use Nevadskiy\Geonames\Support\Geonames\FeatureCode;
use Nevadskiy\Geonames\Tests\Support\Utils\FixtureFileBuilder;

class GeonamesFixture
{
    use WithFaker;

    /**
     * @var FixtureFileBuilder
     */
    private $builder;

    /**
     * DailyDeletesFixture constructor.
     */
    public function __construct(FixtureFileBuilder $builder)
    {
        $this->builder = $builder;
        $this->setUpFaker();
    }

    /**
     * Create fixture file from the given data.
     *
     * @param array $data
     * @return string
     */
    public function create(array $data, string $filename = 'geonames-txt.txt'): string
    {
        return $this->builder->build($filename, $this->mergeData($data));
    }

    /**
     * Merge data with default attributes.
     *
     * @param array $data
     * @return array|array[]
     */
    protected function mergeData(array $data): array
    {
        return array_map(function ($row) {
            return array_merge($this->defaults(), $row);
        }, $data);
    }

    /**
     * Get default attributes.
     *
     * @return array
     */
    protected function defaults(): array
    {
        return [
            'geonameid' => $this->faker->unique()->randomNumber(6),
            'name' => $this->faker->word,
            'asciiname' => $this->faker->word,
            'alternatenames' => '',
            'latitude' => $this->faker->randomFloat(),
            'longitude' => $this->faker->randomFloat(),
            'feature class' => $this->faker->randomElement(['A', 'P']),
            'feature code' => $this->faker->randomElement([FeatureCode::PPLC, FeatureCode::PCLI]),
            'country code' => $this->faker->countryCode,
            'cc2' => '',
            'admin1 code' => '',
            'admin2 code' => '',
            'admin3 code' => '',
            'admin4 code' => '',
            'population' => $this->faker->randomNumber(6),
            'elevation' => '',
            'dem' => '',
            'timezone' => $this->faker->timezone,
            'modification date' => $this->faker->date(),
        ];
    }
}
