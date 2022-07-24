<?php

namespace Nevadskiy\Geonames\Tests\Factories;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Nevadskiy\Geonames\Definitions\FeatureCode;
use Nevadskiy\Geonames\Tests\Models\City;

/**
 * @method City|City[]|Collection create(array $attributes = [])
 */
final class CityFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = City::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->word,
            'country_id' => CountryFactory::new(),
            'division_id' => DivisionFactory::new(),
            'latitude' => $this->faker->latitude,
            'longitude' => $this->faker->longitude,
            'timezone_id' => $this->faker->timezone,
            'population' => $this->faker->randomNumber(6),
            'elevation' => null,
            'dem' => null,
            'feature_code' => FeatureCode::PPL,
            'geoname_id' => $this->faker->unique()->randomNumber(6),
        ];
    }
}
