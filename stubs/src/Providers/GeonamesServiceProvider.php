<?php

namespace App\Providers;

use Nevadskiy\Geonames\Seeders\CitySeeder;
use Nevadskiy\Geonames\Seeders\ContinentSeeder;
use Nevadskiy\Geonames\Seeders\CountrySeeder;
use Nevadskiy\Geonames\Seeders\DivisionSeeder;
use App\Models\Geo\Continent;
use App\Models\Geo\Country;
use App\Models\Geo\Division;
use App\Models\Geo\City;
use Illuminate\Support\ServiceProvider;

class GeonamesServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        ContinentSeeder::useModel(Continent::class);
        CountrySeeder::useModel(Country::class);
        DivisionSeeder::useModel(Division::class);
        CitySeeder::useModel(City::class);
    }
}
