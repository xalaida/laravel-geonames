<?php

namespace Nevadskiy\Geonames\Tests\Support\Factories;

use Illuminate\Foundation\Testing\WithFaker;
use Nevadskiy\Geonames\Geonames;
use Nevadskiy\Geonames\Models\Country;
use Nevadskiy\Geonames\Support\Geonames\FeatureCode;

class CountryFactory
{
    use WithFaker;

    /**
     * Make a new factory instance.
     */
    public function __construct()
    {
        $this->setUpFaker();
    }

    /**
     * Static constructor.
     *
     * @return static
     */
    public static function new(): self
    {
        return new static();
    }

    /**
     * Create a new continent instance and save it into the database.
     */
    public function create(array $attributes = []): Country
    {
        $model = new Country();
        $model->forceFill($this->attributes($attributes));
        $model->save();

        return $model;
    }

    /**
     * Get the merged attributes.
     */
    protected function attributes(array $attributes = []): array
    {
//        collect($this->getDefaults())
//            ->merge($attributes)
//            ->map(function ($value) {
//                return value($value);
//            })
//            ->filter()
//            ->toArray();

        return array_filter(array_map(static function ($attribute) {
            return value($attribute);
        }, array_merge($this->getDefaults(), $attributes)));
    }

    /**
     * Define the model's default state.
     */
    public function getDefaults(): array
    {
        $code = $this->faker->unique()->countryCode;
        $name = $this->faker->country;

        return [
            // 'id' => 'uuid',
            'code' => $code,
            'iso' => $code.'C',
            'iso_numeric' => $this->faker->unique()->randomNumber(3),
            'name' => $name,
            'name_official' => $name,
            'latitude' => $this->faker->randomFloat(),
            'longitude' => $this->faker->randomFloat(),
            'timezone_id' => $this->faker->timezone,
            'continent_id' => function () {
                return app(Geonames::class)->shouldSupplyContinents()
                    ? ContinentFactory::new()->create()->getKey()
                    : null;
            },
            'capital' => null,
            'currency_code' => null,
            'currency_name' => null,
            'tld' => null,
            'phone_code' => null,
            'postal_code_format' => null,
            'postal_code_regex' => null,
            'languages' => null,
            'neighbours' => null,
            'area' => $this->faker->randomFloat(),
            'fips' => null,
            'population' => $this->faker->randomNumber(6),
            'dem' => null,
            'feature_code' => FeatureCode::PCLI,
            'geoname_id' => $this->faker->unique()->randomNumber(6),
            'modified_at' => now(),
        ];
    }
}
