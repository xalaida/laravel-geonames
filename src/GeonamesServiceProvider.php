<?php

namespace Nevadskiy\Geonames;

use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\ServiceProvider;
use Nevadskiy\Geonames\Support\FileReader\BaseFileReader;
use Nevadskiy\Geonames\Support\FileReader\FileReader;

class GeonamesServiceProvider extends ServiceProvider
{
    /**
     * The module's name.
     */
    private const PACKAGE = 'geonames';

    /**
     * Register any module services.
     */
    public function register(): void
    {
        $this->registerConfig();
        $this->registerFileReader();
        $this->registerSeeders();
    }

    /**
     * Bootstrap any module services.
     */
    public function boot(): void
    {
        $this->bootCommands();
        $this->bootMorphMap();
        $this->bootMigrations();
        $this->publishConfig();
        $this->publishMigrations();
        $this->publishResources();
    }

    /**
     * Register any module configurations.
     */
    private function registerConfig(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/geonames.php', self::PACKAGE);
    }

    /**
     * Register the file reader.
     */
    private function registerFileReader(): void
    {
        $this->app->bind(FileReader::class, BaseFileReader::class);
    }

    /**
     * Register any module seeders.
     */
    private function registerSeeders(): void
    {
        foreach ($this->app['config']['geonames']['seeders'] as $seeder => $implementation) {
            $this->app->bind($seeder, $implementation);
        }
    }

    /**
     * Boot any module commands.
     */
    private function bootCommands(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                Console\Seed\SeedContinentsCommand::class,
                Console\Seed\SeedCountriesCommand::class,
                Console\Seed\SeedDivisionsCommand::class,
                Console\Seed\SeedCitiesCommand::class,
                Console\Seed\SeedTranslationsCommand::class,
                Console\Seed\SeedCommand::class,
            ]);
        }
    }

    /**
     * Boot any module migrations.
     */
    private function bootMigrations(): void
    {
        if ($this->app->runningInConsole() && $this->app['config']['geonames']['default_migrations']) {
            $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        }
    }

    /**
     * Boot module morph map.
     */
    private function bootMorphMap(): void
    {
        if ($this->app['config']['geonames']['default_morph_map']) {
            Relation::morphMap([
                'continent' => Models\Continent::class,
                'country' => Models\Country::class,
                'division' => Models\Division::class,
                'city' => Models\City::class,
            ]);
        }
    }

    /**
     * Publish any module configurations.
     */
    private function publishConfig(): void
    {
        $this->publishes([
            __DIR__ . '/../config/geonames.php' => config_path('geonames.php')
        ], self::PACKAGE . '-config');
    }

    /**
     * Publish any module migrations.
     */
    private function publishMigrations(): void
    {
        $this->publishes([
            __DIR__ . '/../database/migrations' => database_path('migrations')
        ], self::PACKAGE . '-migrations');
    }

    /**
     * Publish any module resources.
     */
    private function publishResources(): void
    {
        $this->publishes([
            __DIR__ . '/../resources/meta' => $this->app['config']['geonames']['directory']
        ], self::PACKAGE . '-resources');
    }
}
