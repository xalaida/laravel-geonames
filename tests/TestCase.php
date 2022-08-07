<?php

namespace Nevadskiy\Geonames\Tests;

use Illuminate\Contracts\Config\Repository;
use Illuminate\Foundation\Application;
use Nevadskiy\Geonames\GeonamesServiceProvider;
use Nevadskiy\Geonames\Tests\Support\Assert\DirectoryIsEmpty;
use Nevadskiy\Translatable\TranslatableServiceProvider;
use Orchestra\Testbench\TestCase as OrchestraTestCase;
use PHPUnit\Framework\Constraint\LogicalNot;
use PHPUnit\Framework\ExpectationFailedException;
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

        $this->bootMigrations();

        $this->migrate();
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
     * Boot any testing migrations.
     */
    protected function bootMigrations(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/migrations');
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
        return __DIR__."/fixtures/{$path}";
    }

    /**
     * Asserts that a directory exists.
     *
     * @throws InvalidArgumentException
     * @throws ExpectationFailedException
     */
    protected static function assertDirectoryIsEmpty(string $directory, string $message = ''): void
    {
        static::assertThat($directory, new DirectoryIsEmpty(), $message);
    }

    /**
     * Asserts that a directory does not exist.
     *
     * @throws InvalidArgumentException
     * @throws ExpectationFailedException
     */
    protected static function assertDirectoryIsNotEmpty(string $directory, string $message = ''): void
    {
        static::assertThat($directory, new LogicalNot(new DirectoryIsEmpty()), $message);
    }
}
