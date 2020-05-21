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
            Console\Generate\CountriesResourceCommand::class,
        ]);
    }
}
