<?php

namespace Nevadskiy\Geonames\Tests\Support\Factories;

use Nevadskiy\Geonames\Models\Continent;

class ContinentFactory
{
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
    public function create(array $attributes = []): Continent
    {
        $continent = new Continent();
        $continent->forceFill(array_merge($this->getDefaults(), $attributes));
        $continent->save();

        return $continent;
    }

    /**
     * Get default values.
     */
    private function getDefaults(): array
    {
        return [
            'code' => 'OK',
            'name' => 'Testing continent',
            'latitude' => 60.40,
            'longitude' => 40.60,
            'timezone_id' => 'UTC',
            'population' => 5000,
            'dem' => null,
            'feature_code' => 'CONT',
            'geoname_id' => 15151515,
            'modified_at' => now(),
        ];
    }
}
