<?php

namespace Nevadskiy\Geonames\Tests;

use Illuminate\Foundation\Application;
use Nevadskiy\Geonames\GeonamesServiceProvider;
use Nevadskiy\Geonames\Support\Logger\ConsoleLogger;
use Nevadskiy\Translatable\TranslatableServiceProvider;
use Orchestra\Testbench\TestCase as OrchestraTestCase;
use Psr\Log\NullLogger;

class TestCase extends OrchestraTestCase
{
    /**
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->app->setLocale('en');

        $this->fakeLogger();
    }

    /**
     * Migrate the database.
     */
    protected function migrate(): void
    {
        $this->artisan('migrate', ['--database' => 'testbench'])->run();
    }

    /**
     * Get package providers.
     *
     * @param Application $app
     */
    protected function getPackageProviders($app): array
    {
        return [
            GeonamesServiceProvider::class,
            TranslatableServiceProvider::class,
        ];
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

    /**
     * Get the fixture.
     */
    protected function fixture(string $path): string
    {
        return __DIR__."/Support/fixtures/{$path}";
    }

    /**
     * Fake the logger.
     */
    protected function fakeLogger(): void
    {
        $this->app->instance(ConsoleLogger::class, new NullLogger());
    }
}
