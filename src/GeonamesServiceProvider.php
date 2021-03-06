<?php

namespace Nevadskiy\Geonames;

use Facade\Ignition\QueryRecorder\QueryRecorder;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\ServiceProvider;
use Nevadskiy\Geonames\Events\GeonamesCommandReady;
use Nevadskiy\Geonames\Listeners\DisableIgnitionBindings;
use Nevadskiy\Geonames\Suppliers\CityDefaultSupplier;
use Nevadskiy\Geonames\Suppliers\Translations\TranslationDefaultSeeder;
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
        $this->registerSuppliers();
        $this->registerDefaultCitySupplier();
        $this->registerDefaultTranslationSupplier();
        $this->registerIgnitionFixer();
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
     * Register any module suppliers.
     */
    private function registerSuppliers(): void
    {
        foreach ($this->app['config']['geonames']['suppliers'] as $supplier => $implementation) {
            $this->app->bind($supplier, $implementation);
        }
    }

    /**
     * Register the default city supplier.
     */
    private function registerDefaultCitySupplier(): void
    {
        $this->app->when(CityDefaultSupplier::class)
            ->needs('$minPopulation')
            ->give(function () {
                return $this->app['config']['geonames']['filters']['min_population'];
            });
    }

    /**
     * Register the default translation supplier.
     */
    private function registerDefaultTranslationSupplier(): void
    {
        $this->app->when(TranslationDefaultSeeder::class)
            ->needs('$nullableLanguage')
            ->give(function () {
                return $this->app['config']['geonames']['filters']['nullable_language'];
            });

        $this->app->when(TranslationDefaultSeeder::class)
            ->needs('$languages')
            ->give(function () {
                return $this->app['config']['geonames']['filters']['languages'];
            });
    }

    /**
     * Register ignition memory limit fixer.
     */
    private function registerIgnitionFixer(): void
    {
        if (class_exists(QueryRecorder::class)) {
            $this->app[Dispatcher::class]->listen(GeonamesCommandReady::class, DisableIgnitionBindings::class);
        }
    }

    /**
     * Boot any module commands.
     */
    private function bootCommands(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                Console\Download\DownloadCountriesCommand::class,
                Console\Download\DownloadTranslationsCommand::class,
                Console\Seed\SeedContinentsCommand::class,
                Console\Seed\SeedCountriesCommand::class,
                Console\Seed\SeedDivisionsCommand::class,
                Console\Seed\SeedCitiesCommand::class,
                Console\Seed\SeedTranslationsCommand::class,
                Console\Seed\SeedCommand::class,
                Console\Insert\InsertCommand::class,
                Console\Update\DailyUpdateCommand::class,
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
