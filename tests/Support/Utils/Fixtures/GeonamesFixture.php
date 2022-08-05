<?php

namespace Nevadskiy\Geonames\Tests\Support\Utils\Fixtures;

use Nevadskiy\Geonames\Definitions\FeatureCode;

class GeonamesFixture extends Fixture
{
    /**
     * Get default attributes.
     */
    protected function defaults(): array
    {
        return [
            'geonameid' => $this->faker->unique()->numerify('######'),
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
            'population' => $this->faker->numerify('######'),
            'elevation' => '',
            'dem' => '',
            'timezone' => $this->faker->timezone,
            'modification date' => $this->faker->date(),
        ];
    }

    /**
     * Get the default filename.
     */
    protected function filename(): string
    {
        return 'geonames.txt';
    }
}
