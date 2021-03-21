<?php

namespace Nevadskiy\Geonames\Tests\Integration;

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Schema;
use Nevadskiy\Geonames\Models\Division;
use Nevadskiy\Geonames\Tests\TestCase;

class DisableDivisionsTest extends TestCase
{
    /**
     * Define environment setup.
     *
     * @param Application $app
     */
    protected function getEnvironmentSetUp($app): void
    {
        parent::getEnvironmentSetUp($app);

        $app['config']->set('geonames.models.division', false);
    }

    /** @test */
    public function it_can_disable_division_table(): void
    {
        $this->migrate();
        self::assertFalse(Schema::hasTable(Division::TABLE));
    }
}
