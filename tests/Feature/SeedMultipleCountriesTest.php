<?php

namespace Nevadskiy\Geonames\Tests\Feature;

use Nevadskiy\Geonames\Seeders\CitySeeder;
use Nevadskiy\Geonames\Seeders\CountrySeeder;
use Nevadskiy\Geonames\Seeders\DivisionSeeder;
use Nevadskiy\Geonames\Services\DownloadService;
use Nevadskiy\Geonames\Tests\Models\City;
use Nevadskiy\Geonames\Tests\Models\Continent;
use Nevadskiy\Geonames\Tests\Models\Country;
use Nevadskiy\Geonames\Tests\Models\Division;
use Nevadskiy\Geonames\Tests\TestCase;

// TODO: add possibility to seed continents from NO_COUNTRY dataset (when not allCountries selected)
class SeedMultipleCountriesTest extends TestCase
{
    /** @test */
    public function it_seeds_geonames_dataset_for_several_countries(): void
    {
        config(['geonames.seeders' => [
            CountrySeeder::class,
            DivisionSeeder::class,
            CitySeeder::class,
        ]]);

        CountrySeeder::useModel(Country::class);
        DivisionSeeder::useModel(Division::class);
        CitySeeder::useModel(City::class);

        // TODO: continue working on it when classic structure will be supported

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
