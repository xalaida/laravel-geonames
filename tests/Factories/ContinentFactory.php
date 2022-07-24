<?php

namespace Nevadskiy\Geonames\Tests\Factories;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Nevadskiy\Geonames\Definitions\FeatureCode;
use Nevadskiy\Geonames\Tests\Models\Continent;

/**
 * @method Collection|Continent|Continent[] create(array $attributes = [])
 */
final class ContinentFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Continent::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'code' => $this->faker->unique()->countryCode, // TODO: provide array with real continent codes
            'name' => $this->faker->word, // TODO: provide array with real continent names
            'latitude' => $this->faker->latitude,
            'longitude' => $this->faker->longitude,
            'timezone_id' => $this->faker->timezone,
            'population' => $this->faker->randomNumber(6),
            'dem' => null,
            'feature_code' => FeatureCode::CONT,
            'geoname_id' => $this->faker->unique()->randomNumber(6),
        ];
    }
}
