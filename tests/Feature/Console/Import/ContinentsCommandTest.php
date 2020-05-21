<?php

namespace Nevadskiy\Geonames\Tests\Feature\Console\Import;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Nevadskiy\Geonames\Models\Continent;
use Nevadskiy\Geonames\Tests\TestCase;

class ContinentsCommandTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_imports_continents(): void
    {
        $source = require __DIR__.'/../../../../resources/data/continents.php';

        $this->artisan('geonames:import:continents');

        $this->assertEquals(
            collect($source)->pluck('name', 'geoname_id'),
            Continent::all()->pluck('name', 'geoname_id')
        );
    }
}
