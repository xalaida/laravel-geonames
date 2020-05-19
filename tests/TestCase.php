<?php

namespace Nevadskiy\Geonames\Tests;

use Illuminate\Foundation\Application;
use Nevadskiy\Geonames\GeonamesServiceProvider;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

class TestCase extends OrchestraTestCase
{
    /**
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('migrate', ['--database' => 'testbench'])->run();
    }

    /**
     * Get package providers.
     *
     * @param Application $app
     */
    protected function getPackageProviders($app): array
    {
        return [GeonamesServiceProvider::class];
    }

    /**
     * Define environment setup.
     *
     * @param Application $app
     */
    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
    }
}
