<?php

namespace Nevadskiy\Geonames\Tests\Feature;

use Carbon\Carbon;
use Nevadskiy\Geonames\Seeders\CitySeeder;
use Nevadskiy\Geonames\Seeders\CityTranslationSeeder;
use Nevadskiy\Geonames\Seeders\ContinentSeeder;
use Nevadskiy\Geonames\Seeders\ContinentTranslationSeeder;
use Nevadskiy\Geonames\Seeders\CountrySeeder;
use Nevadskiy\Geonames\Seeders\CountryTranslationSeeder;
use Nevadskiy\Geonames\Seeders\DivisionSeeder;
use Nevadskiy\Geonames\Seeders\DivisionTranslationSeeder;
use Nevadskiy\Geonames\Services\DownloadService;
use Nevadskiy\Geonames\Tests\Factories\CityFactory;
use Nevadskiy\Geonames\Tests\Factories\ContinentFactory;
use Nevadskiy\Geonames\Tests\Factories\CountryFactory;
use Nevadskiy\Geonames\Tests\Factories\DivisionFactory;
use Nevadskiy\Geonames\Tests\Models\City;
use Nevadskiy\Geonames\Tests\Models\Continent;
use Nevadskiy\Geonames\Tests\Models\Country;
use Nevadskiy\Geonames\Tests\Models\Division;
use Nevadskiy\Geonames\Tests\TestCase;

class GeonamesDailyUpdateTest extends TestCase
{
    /** @test */
    public function it_updates_geonames_dataset_according_to_daily_modifications_and_deletes(): void
    {
        // Arrange

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

        $this->travelTo(
            $yesterday = now()->subDay()->startOfDay()
        );

        $invalidContinent = ContinentFactory::new()->create([
            'geoname_id' => 1111111,
            'name' => 'Invalid continent'
        ]);

        $newContinent = ContinentFactory::new()->create([
            'geoname_id' => 6255147,
            'name' => 'Asia',
        ]);

        $oldContinent = ContinentFactory::new()->create([
            'geoname_id' => 6255148,
            'name' => 'Old Europe',
        ]);

        $invalidCountry = CountryFactory::new()->create([
            'geoname_id' => 2222222,
            'continent_id' => $oldContinent->getKey(),
            'name' => 'Invalid country'
        ]);

        $newCountry = CountryFactory::new()->create([
            'geoname_id' => 1861060,
            'continent_id' => $newContinent->getKey(),
            'name' => 'Japan',
        ]);

        $oldCountry = CountryFactory::new()->create([
            'geoname_id' => 690791,
            'continent_id' => $oldContinent->getKey(),
            'name' => 'Old Ukraine',
        ]);

        $invalidDivision = DivisionFactory::new()->create([
            'geoname_id' => 3333333,
            'country_id' => $oldCountry->getKey(),
            'name' => 'Invalid division'
        ]);

        $newDivision = DivisionFactory::new()->create([
            'geoname_id' => 1850144,
            'country_id' => $newCountry->getKey(),
            'name' => 'Tokyo',
        ]);

        $oldDivision = DivisionFactory::new()->create([
            'geoname_id' => 703883,
            'country_id' => $oldCountry->getKey(),
            'name' => 'Old Crimea',
        ]);

        $invalidCity = CityFactory::new()->create([
            'geoname_id' => 4444444,
            'country_id' => $oldCountry->getKey(),
            'division_id' => $oldDivision->getKey(),
            'name' => 'Invalid city'
        ]);

        $newCity = CityFactory::new()->create([
            'geoname_id' => 1850147,
            'country_id' => $newCountry->getKey(),
            'division_id' => $newDivision->getKey(),
            'name' => 'Tokyo',
        ]);

        $oldCity = CityFactory::new()->create([
            'geoname_id' => 694423,
            'country_id' => $oldCountry->getKey(),
            'division_id' => $oldDivision->getKey(),
            'name' => 'Old Sevastopol',
        ]);

        // TODO: add same for alternate names

        $this->assertDatabaseCount('continents', 3);
        $this->assertDatabaseCount('countries', 3);
        $this->assertDatabaseCount('divisions', 3);
        $this->assertDatabaseCount('cities', 3);

        $this->travelTo(
            $today = now()->addDay()->startOfDay()
        );

        $service = $this->mock(DownloadService::class);

        $service->shouldReceive('downloadCountryInfo')
            ->andReturn($this->fixture('countryInfo.txt'));

        $service->shouldReceive('downloadDailyModifications')
            ->andReturn($this->fixture('dailyModifications.txt'));

        $service->shouldReceive('downloadDailyDeletes')
            ->andReturn($this->fixture('dailyDeletes.txt'));

        $service->shouldReceive('downloadDailyAlternateNamesModifications')
            ->andReturn($this->fixture('alternateNamesDailyModifications.txt'));

        $service->shouldReceive('downloadDailyAlternateNamesDeletes')
            ->andReturn($this->fixture('alternateNamesDailyDeletes.txt'));

        // Act

        $this->artisan('geonames:daily-update');

        // Asserts

        $this->assertDatabaseCount('continents', 3);

        $this->assertModelMissing($invalidContinent);

        $this->assertDatabaseHas('continents', [
            'geoname_id' => 6255147,
            'name' => 'Asia',
            'updated_at' => $yesterday,
        ]);

        $this->assertDatabaseHas('continents', [
            'geoname_id' => 6255148,
            'name' => 'Europe',
            'updated_at' => $this->modificationDate('2019-08-12'),
        ]);

        $this->assertDatabaseHas('continents', [
            'geoname_id' => 6255149,
            'name' => 'North America',
            'updated_at' => $this->modificationDate('2019-08-12'),
        ]);

        $this->assertDatabaseCount('countries', 3);

        $this->assertModelMissing($invalidCountry);

        $this->assertDatabaseHas('countries', [
            'geoname_id' => 1861060,
            'name' => 'Japan',
            'updated_at' => $yesterday,
        ]);

        $this->assertDatabaseHas('countries', [
            'geoname_id' => 690791,
            'name' => 'Ukraine',
            'updated_at' => $this->modificationDate('2021-08-16'),
        ]);

        $this->assertDatabaseHas('countries', [
            'geoname_id' => 6252001,
            'name' => 'United States',
            'updated_at' => $this->modificationDate('2022-04-06'),
        ]);

        $this->assertDatabaseCount('divisions', 3);

        $this->assertModelMissing($invalidDivision);

        $this->assertDatabaseHas('divisions', [
            'geoname_id' => 1850144,
            'name' => 'Tokyo',
            'updated_at' => $yesterday,
        ]);

        $this->assertDatabaseHas('divisions', [
            'geoname_id' => 703883,
            'name' => 'Autonomous Republic of Crimea',
            'updated_at' => $this->modificationDate('2020-09-01'),
        ]);

        $this->assertDatabaseHas('divisions', [
            'geoname_id' => 4138106,
            'name' => 'District of Columbia',
            'updated_at' => $this->modificationDate('2022-03-09'),
        ]);

        $this->assertDatabaseCount('cities', 3);

        $this->assertModelMissing($invalidCity);

        $this->assertDatabaseHas('cities', [
            'geoname_id' => 1850147,
            'name' => 'Tokyo',
            'updated_at' => $yesterday,
        ]);

        $this->assertDatabaseHas('cities', [
            'geoname_id' => 694423,
            'name' => 'Sevastopol',
            'updated_at' => $this->modificationDate('2022-04-01'),
        ]);

        $this->assertDatabaseHas('cities', [
            'geoname_id' => 4140963,
            'name' => 'Washington',
            'updated_at' => $this->modificationDate('2022-05-02'),
        ]);
    }

    /**
     * Get the modification date instance.
     */
    protected function modificationDate(string $date): Carbon
    {
        return Carbon::createFromFormat('Y-m-d', $date)->startOfDay();
    }
}
