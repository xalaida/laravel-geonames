<?php

namespace Nevadskiy\Geonames\Tests\Feature;

use Nevadskiy\Geonames\Seeders\CitySeeder;
use Nevadskiy\Geonames\Seeders\ContinentSeeder;
use Nevadskiy\Geonames\Seeders\CountrySeeder;
use Nevadskiy\Geonames\Seeders\DivisionSeeder;
use Nevadskiy\Geonames\Services\DownloadService;
use Nevadskiy\Geonames\Tests\Models\City;
use Nevadskiy\Geonames\Tests\Models\Continent;
use Nevadskiy\Geonames\Tests\Models\Country;
use Nevadskiy\Geonames\Tests\Models\Division;
use Nevadskiy\Geonames\Tests\TestCase;

class GeonamesSeedTest extends TestCase
{
    /** @test */
    public function it_seeds_geonames_dataset_into_database(): void
    {
        // TODO: add seeding alternate names...

        config(['geonames.seeders' => [
            ContinentSeeder::class,
            CountrySeeder::class,
            DivisionSeeder::class,
            CitySeeder::class,
        ]]);

        ContinentSeeder::useModel(Continent::class);
        CountrySeeder::useModel(Country::class);
        DivisionSeeder::useModel(Division::class);
        CitySeeder::useModel(City::class);

        $service = $this->mock(DownloadService::class);

        $service->shouldReceive('downloadCountryInfo')
            ->andReturn($this->fixture('countryInfo.txt'));

        $service->shouldReceive('downloadAllCountries')
            ->andReturn($this->fixture('allCountries.txt'));

        $service->shouldReceive('downloadAlternateNames')
            ->andReturn($this->fixture('alternateNames.txt'));

        $this->artisan('geonames:seed');

        $this->assertDatabaseCount(Continent::class, 1);
        $this->assertDatabaseCount(Country::class, 2);
        $this->assertDatabaseCount(Division::class, 8);
        $this->assertDatabaseCount(City::class, 9);
    }
}
