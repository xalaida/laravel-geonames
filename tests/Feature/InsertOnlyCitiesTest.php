<?php

namespace Nevadskiy\Geonames\Tests\Feature;

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Schema;
use Nevadskiy\Geonames\Models\City;
use Nevadskiy\Geonames\Models\Continent;
use Nevadskiy\Geonames\Models\Country;
use Nevadskiy\Geonames\Models\Division;
use Nevadskiy\Geonames\Services\DownloadService;
use Nevadskiy\Geonames\Tests\TestCase;
use Nevadskiy\Translatable\Models\Translation;

class InsertOnlyCitiesTest extends TestCase
{
    /**
     * Define environment setup.
     *
     * @param Application $app
     */
    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('geonames.source', DownloadService::SOURCE_ONLY_CITIES);
        $app['config']->set('geonames.filters.population', 15000);
        $app['config']->set('geonames.filters.countries', ['AE']);
        $app['config']->set('geonames.translations', true);
        $app['config']->set('geonames.languages', ['de']);

        parent::getEnvironmentSetUp($app);
    }

    /** @test */
    public function it_can_insert_only_cities_into_database(): void
    {
        $this->fakeDownloadService();
        $this->fakeDirectoryCleaner();

        $this->migrate();

        $this->artisan('geonames:insert');

        self::assertFalse(Schema::hasTable(Continent::TABLE));
        self::assertFalse(Schema::hasTable(Country::TABLE));
        self::assertFalse(Schema::hasTable(Division::TABLE));
        self::assertTrue(Schema::hasTable(City::TABLE));
        self::assertCount(1, City::all());
        self::assertCount(1, Translation::all());
    }
}
