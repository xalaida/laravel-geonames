<?php

namespace Nevadskiy\Geonames\Tests\Factories;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Nevadskiy\Geonames\Definitions\FeatureCode;
use Nevadskiy\Geonames\Tests\Models\Division;

/**
 * @method Collection|Division|Division[] create(array $attributes = [])
 */
final class DivisionFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Division::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->word,
            'country_id' => CountryFactory::new(),
            'latitude' => $this->faker->latitude,
            'longitude' => $this->faker->longitude,
            'timezone_id' => $this->faker->timezone,
            'population' => $this->faker->numerify('######'),
            'elevation' => null,
            'dem' => null,
            'code' => $this->faker->randomNumber(2),
            'feature_code' => FeatureCode::ADM1,
            'geoname_id' => $this->faker->unique()->numerify('######'),
        ];
    }
}
