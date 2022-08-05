<?php

namespace Nevadskiy\Geonames;

use Illuminate\Console\OutputStyle;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Nevadskiy\Downloader\CurlDownloader;
use Nevadskiy\Downloader\Downloader;
use Nevadskiy\Geonames\Downloader\ConsoleProgressDownloader;
use Nevadskiy\Geonames\Downloader\HistoryDownloader;
use Nevadskiy\Geonames\Downloader\UnzipDownloader;
use Nevadskiy\Geonames\Downloader\Unzipper;
use Nevadskiy\Geonames\Reader\ConsoleProgressReader;
use Nevadskiy\Geonames\Reader\FileReader;
use Nevadskiy\Geonames\Reader\Reader;
use Nevadskiy\Geonames\Support\OutputFactory;
use Symfony\Component\Finder\SplFileInfo;

class GeonamesServiceProvider extends ServiceProvider
{
    /**
     * Register any package services.
     */
    public function register(): void
    {
        $this->registerConfig();
        $this->registerGeonamesDownloader();
        $this->registerFileDownloader();
        $this->registerFileReader();
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
     * Register the geonames downloader instance.
     */
    private function registerGeonamesDownloader(): void
    {
        $this->app->when(GeonamesDownloader::class)
            ->needs('$directory')
            ->give(function () {
                return config('geonames.directory');
            });
    }

    private function registerFileDownloader(): void
    {
        $this->app->singleton(Downloader::class, function (Application $app) {
            $downloader = new CurlDownloader();
            $downloader->updateIfExists();
            $downloader->allowDirectoryCreation();

            return $downloader;
        });

        if ($this->app->runningInConsole()) {
            $this->app->extend(Downloader::class, function (CurlDownloader $downloader, Application $app) {
                // TODO: consider tagging OutputAwareInterface and swap output in console command just by tag.
                return new ConsoleProgressDownloader($downloader, $this->getOutput());
            });
        }

        $this->app->extend(Downloader::class, function (Downloader $downloader, Application $app) {
            return new UnzipDownloader($downloader, new Unzipper());
        });

        $this->app->extend(Downloader::class, function (Downloader $downloader, Application $app) {
            return new HistoryDownloader($downloader);
        });
    }

    private function registerFileReader(): void
    {
        $this->app->singleton(Reader::class, function (Application $app) {
            return new FileReader();
        });

        if ($this->app->runningInConsole()) {
            $this->app->extend(Reader::class, function (Reader $reader, Application $app) {
                // TODO: consider tagging OutputAwareInterface and swap output in console command just by tag.
                return new ConsoleProgressReader($reader, $this->getOutput());
            });
        }
    }

    private function getOutput(): OutputStyle
    {
        return OutputFactory::make();
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
