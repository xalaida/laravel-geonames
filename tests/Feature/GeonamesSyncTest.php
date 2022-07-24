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
use Nevadskiy\Geonames\Tests\Factories\CityTranslationFactory;
use Nevadskiy\Geonames\Tests\Factories\ContinentFactory;
use Nevadskiy\Geonames\Tests\Factories\ContinentTranslationFactory;
use Nevadskiy\Geonames\Tests\Factories\CountryFactory;
use Nevadskiy\Geonames\Tests\Factories\CountryTranslationFactory;
use Nevadskiy\Geonames\Tests\Factories\DivisionFactory;
use Nevadskiy\Geonames\Tests\Factories\DivisionTranslationFactory;
use Nevadskiy\Geonames\Tests\Models\City;
use Nevadskiy\Geonames\Tests\Models\Continent;
use Nevadskiy\Geonames\Tests\Models\Country;
use Nevadskiy\Geonames\Tests\Models\Division;
use Nevadskiy\Geonames\Tests\TestCase;

class GeonamesSyncTest extends TestCase
{
    /** @test */
    public function it_syncs_geonames_dataset(): void
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

        $oldContinent = ContinentFactory::new()->create([
            'geoname_id' => 6255148,
            'name' => '...',
        ]);

        $invalidTranslationForOldContinent = ContinentTranslationFactory::new()->create([
            'continent_id' => $oldContinent->getKey(),
            'alternate_name_id' => 1111111,
            'name' => 'Invalid continent translation',
            'locale' => 'en',
        ]);

        $oldTranslationForOldContinent = ContinentTranslationFactory::new()->create([
            'continent_id' => $oldContinent->getKey(),
            'alternate_name_id' => 7825137,
            'name' => '...',
            'locale' => 'uk',
        ]);

        $invalidCountry = CountryFactory::new()->create([
            'geoname_id' => 2222222,
            'continent_id' => $oldContinent->getKey(),
            'name' => 'Invalid country'
        ]);

        $oldCountry = CountryFactory::new()->create([
            'geoname_id' => 690791,
            'continent_id' => $oldContinent->getKey(),
            'name' => '...',
        ]);

        $invalidTranslationForOldCountry = CountryTranslationFactory::new()->create([
            'country_id' => $oldCountry->getKey(),
            'alternate_name_id' => 2222222,
            'name' => 'Invalid country translation',
            'locale' => 'en',
        ]);

        $oldTranslationForOldCountry = CountryTranslationFactory::new()->create([
            'country_id' => $oldCountry->getKey(),
            'alternate_name_id' => 1564467,
            'name' => '...',
            'locale' => 'uk',
        ]);

        $invalidDivision = DivisionFactory::new()->create([
            'geoname_id' => 3333333,
            'country_id' => $oldCountry->getKey(),
            'name' => 'Invalid division'
        ]);

        $oldDivision = DivisionFactory::new()->create([
            'geoname_id' => 703883,
            'country_id' => $oldCountry->getKey(),
            'name' => '...',
        ]);

        $invalidTranslationForOldDivision = DivisionTranslationFactory::new()->create([
            'division_id' => $oldDivision->getKey(),
            'alternate_name_id' => 3333333,
            'name' => 'Invalid division translation',
            'locale' => 'en',
        ]);

        $oldTranslationForOldDivision = DivisionTranslationFactory::new()->create([
            'division_id' => $oldDivision->getKey(),
            'alternate_name_id' => 2432644,
            'name' => '...',
            'locale' => 'uk',
        ]);

        $invalidCity = CityFactory::new()->create([
            'geoname_id' => 4444444,
            'country_id' => $oldCountry->getKey(),
            'division_id' => $oldDivision->getKey(),
            'name' => 'Invalid city'
        ]);

        $oldCity = CityFactory::new()->create([
            'geoname_id' => 694423,
            'country_id' => $oldCountry->getKey(),
            'division_id' => $oldDivision->getKey(),
            'name' => '...',
        ]);

        $invalidTranslationForOldCity = CityTranslationFactory::new()->create([
            'city_id' => $oldCity->getKey(),
            'alternate_name_id' => 4444444,
            'name' => 'Invalid city translation',
            'locale' => 'en',
        ]);

        $oldTranslationForOldCity = CityTranslationFactory::new()->create([
            'city_id' => $oldCity->getKey(),
            'alternate_name_id' => 1634381,
            'name' => '...',
            'locale' => 'uk',
        ]);

        $this->travelTo(
            $today = now()->addDay()->startOfDay()
        );

        $service = $this->mock(DownloadService::class);

        $service->shouldReceive('downloadCountryInfo')
            ->andReturn($this->fixture('countryInfo.txt'));

        $service->shouldReceive('downloadAllCountries')
            ->andReturn($this->fixture('allCountries.txt'));

        $service->shouldReceive('downloadAlternateNamesV2')
            ->andReturn($this->fixture('alternateNamesV2.txt'));

        // Act
        $this->artisan('geonames:sync');

        // Asserts
        $this->assertDatabaseCount('continents', 1);

        $this->assertModelMissing($invalidContinent);

        $this->assertDatabaseHas('continents', [
            $oldContinent->getKeyName() => $oldContinent->getKey(),
            'geoname_id' => 6255148,
            'name' => 'Europe',
            'updated_at' => $this->modificationDate('2019-08-12'),
        ]);

        $this->assertDatabaseCount('continent_translations', 3);

        $this->assertModelMissing($invalidTranslationForOldContinent);

        $this->assertDatabaseHas('continent_translations', [
            $oldTranslationForOldContinent->getKeyName() => $oldTranslationForOldContinent->getKey(),
            'alternate_name_id' => 7825137,
            'name' => 'Європа',
            'locale' => 'uk',
            'updated_at' => $today,
        ]);

        $this->assertDatabaseHas('continent_translations', [
            'alternate_name_id' => 2039205,
            'name' => 'Europa',
            'locale' => 'pl',
            'updated_at' => $today
        ]);

        $this->assertDatabaseCount('countries', 2);

        $this->assertModelMissing($invalidCountry);

        $this->assertDatabaseHas('countries', [
            $oldCountry->getKeyName() => $oldCountry->getKey(),
            'geoname_id' => 690791,
            'name' => 'Ukraine',
            'updated_at' => $this->modificationDate('2021-08-16'),
        ]);

        $this->assertDatabaseCount('country_translations', 6);

        $this->assertModelMissing($invalidTranslationForOldCountry);

        $this->assertDatabaseHas('country_translations', [
            $oldTranslationForOldCountry->getKeyName() => $oldTranslationForOldCountry->getKey(),
            'alternate_name_id' => 1564467,
            'name' => 'Україна',
            'locale' => 'uk',
            'updated_at' => $today,
        ]);

        $this->assertDatabaseHas('country_translations', [
            'alternate_name_id' => 1564455,
            'name' => 'Ukraina',
            'locale' => 'pl',
            'updated_at' => $today
        ]);

        $this->assertDatabaseCount('divisions', 8);

        $this->assertModelMissing($invalidDivision);

        $this->assertDatabaseHas('divisions', [
            $oldDivision->getKeyName() => $oldDivision->getKey(),
            'geoname_id' => 703883,
            'name' => 'Autonomous Republic of Crimea',
            'updated_at' => $this->modificationDate('2020-09-01'),
        ]);

        $this->assertDatabaseCount('division_translations', 19);

        $this->assertModelMissing($invalidTranslationForOldDivision);

        $this->assertDatabaseHas('division_translations', [
            $oldTranslationForOldDivision->getKeyName() => $oldTranslationForOldDivision->getKey(),
            'alternate_name_id' => 2432644,
            'name' => 'Республіка Крим',
            'locale' => 'uk',
            'updated_at' => $today,
        ]);

        $this->assertDatabaseHas('division_translations', [
            'alternate_name_id' => 13701145,
            'name' => 'Republika Autonomiczna Krymu',
            'locale' => 'pl',
            'updated_at' => $today
        ]);

        $this->assertDatabaseCount('cities', 9);

        $this->assertModelMissing($invalidCity);

        $this->assertDatabaseHas('cities', [
            $oldCity->getKeyName() => $oldCity->getKey(),
            'geoname_id' => 694423,
            'name' => 'Sevastopol',
            'updated_at' => $this->modificationDate('2022-04-01'),
        ]);

        $this->assertDatabaseCount('city_translations', 27);

        $this->assertModelMissing($invalidTranslationForOldCity);

        $this->assertDatabaseHas('city_translations', [
            $oldTranslationForOldCity->getKeyName() => $oldTranslationForOldCity->getKey(),
            'alternate_name_id' => 1634381,
            'name' => 'Севастополь',
            'locale' => 'uk',
            'updated_at' => $today,
        ]);

        $this->assertDatabaseHas('city_translations', [
            'alternate_name_id' => 1634356,
            'name' => 'Sewastopol',
            'locale' => 'pl',
            'updated_at' => $today
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
