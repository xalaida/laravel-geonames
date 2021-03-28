<?php

namespace Nevadskiy\Geonames\Tests;

use Illuminate\Foundation\Application;
use Nevadskiy\Geonames\GeonamesServiceProvider;
use Nevadskiy\Geonames\Services\DownloadService;
use Nevadskiy\Geonames\Support\Cleaner\DirectoryCleaner;
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
        $this->app->instance(ConsoleLogger::class, new NullLogger);
    }

    /**
     * Fake the directory cleaner.
     */
    protected function fakeDirectoryCleaner(): void
    {
        $directoryCleaner = $this->mock(DirectoryCleaner::class);

        $directoryCleaner->shouldReceive('keepGitignore')
            ->once()
            ->withNoArgs()
            ->andReturnSelf();

        $directoryCleaner->shouldReceive('clean')
            ->once()
            ->with(config('geonames.directory'));
    }

    /**
     * Fake download service.
     */
    protected function fakeDownloadService(): void
    {
        $downloadService = $this->mock(DownloadService::class);

        $downloadService->shouldReceive('downloadCountryInfo')
            ->withNoArgs()
            ->andReturn($this->fixture('countryInfo.txt'));

        $downloadService->shouldReceive('downloadSourceFiles')
            ->withNoArgs()
            ->andReturn([$this->fixture('allCountries.txt')]);

        $downloadService->shouldReceive('downloaderAlternateNames')
            ->withNoArgs()
            ->andReturn([$this->fixture('alternateNames.txt')]);
    }
}
