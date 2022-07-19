<?php

namespace Nevadskiy\Geonames\Tests\Feature;

use Nevadskiy\Geonames\Seeders\CitySeeder;
use Nevadskiy\Geonames\Seeders\CityTranslationSeeder;
use Nevadskiy\Geonames\Seeders\ContinentSeeder;
use Nevadskiy\Geonames\Seeders\ContinentTranslationSeeder;
use Nevadskiy\Geonames\Seeders\CountrySeeder;
use Nevadskiy\Geonames\Seeders\CountryTranslationSeeder;
use Nevadskiy\Geonames\Seeders\DivisionSeeder;
use Nevadskiy\Geonames\Seeders\DivisionTranslationSeeder;
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
            ContinentTranslationSeeder::class,
            CountrySeeder::class,
            CountryTranslationSeeder::class,
            DivisionSeeder::class,
            DivisionTranslationSeeder::class,
            CitySeeder::class,
            CityTranslationSeeder::class,
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

        $service->shouldReceive('downloadAlternateNamesV2')
            ->andReturn($this->fixture('alternateNamesV2.txt'));

        $this->artisan('geonames:seed');

        $this->assertDatabaseCount('continents', 1);
        $this->assertDatabaseCount('continent_translations', 3);
        $this->assertDatabaseCount('countries', 2);
        $this->assertDatabaseCount('country_translations', 6);
        $this->assertDatabaseCount('divisions', 8);
        $this->assertDatabaseCount('division_translations', 19);
        $this->assertDatabaseCount('cities', 9);
        $this->assertDatabaseCount('city_translations', 27);
    }
}
