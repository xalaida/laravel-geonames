<?php

namespace Nevadskiy\Geonames\Tests;

use Illuminate\Contracts\Config\Repository;
use Illuminate\Foundation\Application;
use Nevadskiy\Geonames\GeonamesServiceProvider;
use Nevadskiy\Geonames\Services\DownloadService;
use Nevadskiy\Geonames\Support\Cleaner\DirectoryCleaner;
use Nevadskiy\Geonames\Support\Logger\ConsoleLogger;
use Nevadskiy\Geonames\Tests\Support\Assert\DirectoryIsEmpty;
use Nevadskiy\Translatable\TranslatableServiceProvider;
use Orchestra\Testbench\TestCase as OrchestraTestCase;
use PHPUnit\Framework\Constraint\LogicalNot;
use PHPUnit\Framework\ExpectationFailedException;
use Psr\Log\NullLogger;
use SebastianBergmann\RecursionContext\InvalidArgumentException;

class TestCase extends OrchestraTestCase
{
    /**
     * Default configurations.
     *
     * @var array
     */
    protected $config = [];

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
        $config = $app['config'];

        $this->configureDatabase($config);
        $this->configurePackage($config);
    }

    /**
     * Configure the testing database.
     */
    protected function configureDatabase(Repository $config): void
    {
        $config->set('database.default', 'testbench');

        $config->set('database.connections.testbench', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
    }

    /**
     * Configure the package.
     */
    protected function configurePackage(Repository $config): void
    {
        foreach ($this->config as $key => $value) {
            $config->set($key, $value);
        }
    }

    /**
     * Migrate the database.
     */
    protected function migrate(): void
    {
        $this->artisan('migrate', ['--database' => 'testbench'])->run();
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

    /**
     * Asserts that a directory exists.
     *
     * @throws InvalidArgumentException
     * @throws ExpectationFailedException
     */
    public static function assertDirectoryIsEmpty(string $directory, string $message = ''): void
    {
        static::assertThat($directory, new DirectoryIsEmpty(), $message);
    }

    /**
     * Asserts that a directory does not exist.
     *
     * @throws InvalidArgumentException
     * @throws ExpectationFailedException
     */
    public static function assertDirectoryIsNotEmpty(string $directory, string $message = ''): void
    {
        static::assertThat($directory, new LogicalNot(new DirectoryIsEmpty()), $message);
    }
}
