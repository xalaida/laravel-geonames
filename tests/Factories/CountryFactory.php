<?php

namespace Nevadskiy\Geonames\Tests\Factories;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Nevadskiy\Geonames\Definitions\FeatureCode;
use Nevadskiy\Geonames\Tests\Models\Country;

/**
 * @method Collection|Country|Country[] create(array $attributes = [])
 */
final class CountryFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Country::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'code' => $code = $this->faker->unique()->asciify('**'),
            'iso' => "{$code}C",
            'iso_numeric' => $this->faker->unique()->numerify('###'),
            'name' => $name = $this->faker->country,
            'name_official' => $name,
            'latitude' => $this->faker->latitude,
            'longitude' => $this->faker->longitude,
            'timezone_id' => $this->faker->timezone,
            'continent_id' => ContinentFactory::new(),
            'capital' => null,
            'tld' => null,
            'phone_code' => null,
            'postal_code_format' => null,
            'postal_code_regex' => null,
            'languages' => null,
            'neighbours' => null,
            'area' => $this->faker->randomFloat(),
            'fips' => null,
            'population' => $this->faker->numerify('######'),
            'dem' => null,
            'feature_code' => FeatureCode::PCLI,
            'geoname_id' => $this->faker->unique()->numerify('######'),
        ];
    }
}
