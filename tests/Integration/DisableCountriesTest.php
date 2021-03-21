<?php

namespace Nevadskiy\Geonames\Tests\Integration;

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Schema;
use Nevadskiy\Geonames\Models\Country;
use Nevadskiy\Geonames\Tests\TestCase;

class DisableCountriesTest extends TestCase
{
    /**
     * Define environment setup.
     *
     * @param  Application  $app
     *
     * @return void
     */
    protected function getEnvironmentSetUp($app): void
    {
        parent::getEnvironmentSetUp($app);

        $app['config']->set('geonames.models.country', false);
    }

    /** @test */
    public function it_can_disable_countries_table(): void
    {
        $this->migrate();
        self::assertFalse(Schema::hasTable(Country::TABLE));
    }
}
