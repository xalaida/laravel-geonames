<?php

namespace Nevadskiy\Geonames;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Symfony\Component\Finder\SplFileInfo;

class GeonamesServiceProvider extends ServiceProvider
{
    /**
     * Register any package services.
     */
    public function register(): void
    {
        $this->registerConfig();
    }

    /**
     * Bootstrap any package services.
     */
    public function boot(): void
    {
        $this->bootCommands();
        $this->publishConfig();
        $this->publishMigrations();
        $this->publishModels();
        $this->publishSeeders();
    }

    /**
     * Register any package configurations.
     */
    protected function registerConfig(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/geonames.php', 'geonames');
    }

    /**
     * Boot any package commands.
     */
    protected function bootCommands(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                Console\GeonamesSeedCommand::class,
                Console\GeonamesSyncCommand::class,
                Console\GeonamesDailyUpdateCommand::class,
            ]);
        }
    }

    /**
     * Publish any package configurations.
     */
    protected function publishConfig(): void
    {
        $this->publishes([
            __DIR__.'/../config/geonames.php' => config_path('geonames.php'),
        ], 'geonames-config');
    }

    /**
     * Publish any package migrations.
     */
    protected function publishMigrations(): void
    {
        $this->publishes($this->stubPaths('database/migrations'), 'geonames-migrations');
    }

    /**
     * Publish any package models.
     */
    protected function publishModels(): void
    {
        $this->publishes($this->stubPaths('app/Models/Geo'), 'geonames-models');
    }

    /**
     * Publish any package seeders.
     */
    protected function publishSeeders(): void
    {
        $this->publishes($this->stubPaths('app/Seeders'), 'geonames-seeders');
    }

    /**
     * Get the stub paths for publishing by the given path.
     */
    protected function stubPaths(string $path): array
    {
        $path = trim($path, '/');

        return collect((new Filesystem())->allFiles(__DIR__.'/../stubs/'.$path))
            ->mapWithKeys(function (SplFileInfo $file) use ($path) {
                return [$file->getPathname() => base_path($path.'/'.Str::replaceLast('.stub', '.php', $file->getFilename()))];
            })
            ->all();
    }
}
