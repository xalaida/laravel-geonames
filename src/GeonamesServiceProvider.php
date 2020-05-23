<?php

namespace Nevadskiy\Geonames;

use Illuminate\Support\ServiceProvider;

class GeonamesServiceProvider extends ServiceProvider
{
    /**
     * Register any package services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any package services.
     */
    public function boot(): void
    {
        $this->bootMigrations();
        $this->bootCommands();
        $this->publishMigrations();
    }

    /**
     * Boot any package migrations.
     */
    private function bootMigrations(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    }

    /**
     * Boot any package console commands.
     */
    private function bootCommands(): void
    {
        $this->commands([
            Console\Import\ContinentsCommand::class,
            Console\Import\CountriesCommand::class,
            Console\Import\TimezonesCommand::class,
            Console\Import\CitiesCommand::class,
            Console\InstallCommand::class,
            Console\Generate\CountriesResourceCommand::class,
        ]);
    }

    /**
     * Boot the package migrations publisher.
     */
    private function publishMigrations(): void
    {
        $this->publishes([
            __DIR__.'/../database/migrations' => database_path('migrations'),
        ], 'geoname-migrations');
    }
}
