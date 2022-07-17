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

        $this->mock(DownloadService::class)
            ->shouldReceive('downloadAllCountries')
            ->andReturn($this->fixture('allCountries.txt'))
            ->getMock()
            ->shouldReceive('downloadCountryInfo')
            ->andReturn($this->fixture('countryInfo.txt'))
            ->getMock()
            ->shouldReceive('downloadAlternateNames')
            ->andReturn($this->fixture('alternateNames.txt'));

        $this->artisan('geonames:seed');

        self::assertCount(3, Continent::all());
        self::assertCount(1, Country::all());
        self::assertCount(1, Division::all());
        self::assertCount(1, City::all());
    }
}
