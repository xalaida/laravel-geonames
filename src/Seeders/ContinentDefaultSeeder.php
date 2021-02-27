<?php

namespace Nevadskiy\Geonames\Seeders;

use Nevadskiy\Geonames\Models\Continent;

class ContinentDefaultSeeder implements ContinentSeeder
{
    /**
     * @inheritDoc
     */
    public function seed(array $continent, int $id): void
    {
        Continent::query()->updateOrCreate(['geoname_id' => $id], [
            'name' => $continent['name'],
            'latitude' => $continent['latitude'],
            'longitude' => $continent['longitude'],
            'timezone_id' => $continent['timezone'],
            'population' => $continent['population'],
            'dem' => $continent['dem'],
            'geoname_id' => $id,
            'modified_at' => $continent['modification date'],
        ]);
    }
}
