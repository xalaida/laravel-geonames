<?php

namespace Nevadskiy\Geonames\Tests\Factories;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Nevadskiy\Geonames\Translations\Translation;

/**
 * @method Collection|Translation|Translation[] create(array $attributes = [])
 */
final class ContinentTranslationFactory extends Factory
{
    /**
     * @inheritdoc
     */
    public function newModel(array $attributes = []): Model
    {
        $model = new Translation($attributes);

        $model->setTable('continent_translations');

        return $model;
    }

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'continent_id' => ContinentFactory::new(),
            'name' => $this->faker->word(),
            'is_preferred' => $this->faker->boolean(),
            'is_short' => $this->faker->boolean(),
            'is_colloquial' => $this->faker->boolean(),
            'is_historic' => $this->faker->boolean(),
            'locale' => $this->faker->locale(),
            'alternate_name_id' => $this->faker->unique()->numerify('######'),
        ];
    }
}
