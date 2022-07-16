<?php

namespace Nevadskiy\Geonames;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Nevadskiy\Geonames\Parsers\FileParser;
use Nevadskiy\Geonames\Parsers\Parser;
use Nevadskiy\Geonames\Parsers\ProgressParser;
use Nevadskiy\Geonames\Support\Downloader\BaseDownloader;
use Nevadskiy\Geonames\Support\Downloader\ConsoleDownloader;
use Nevadskiy\Geonames\Support\Downloader\Downloader;
use Nevadskiy\Geonames\Support\Downloader\UnzipperDownloader;
use Nevadskiy\Geonames\Support\FileReader\BaseFileReader;
use Nevadskiy\Geonames\Support\FileReader\FileReader;
use Nevadskiy\Geonames\Support\Logger\ConsoleLogger;
use Nevadskiy\Geonames\Support\Output\OutputFactory;
use Psr\Log\LoggerInterface;
use Symfony\Component\Finder\SplFileInfo;

class GeonamesServiceProvider extends ServiceProvider
{
    /**
     * Register any package services.
     */
    public function register(): void
    {
        $this->registerConfig();
        $this->registerLogger();
        $this->registerDownloader();
        $this->registerFileReader();
        $this->registerParser();
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
     * Register any package logger.
     */
    protected function registerLogger(): void
    {
        $this->app->singleton(ConsoleLogger::class, function () {
            return new ConsoleLogger(OutputFactory::make());
        });

        if ($this->app->runningInConsole()) {
            $this->app->when(BaseDownloader::class)
                ->needs(LoggerInterface::class)
                ->give(ConsoleLogger::class);
        }
    }

    /**
     * Register any package downloader.
     */
    protected function registerDownloader(): void
    {
        $this->app->bind(Downloader::class, BaseDownloader::class);

        $this->app->extend(Downloader::class, function (Downloader $downloader) {
            return $this->app->make(UnzipperDownloader::class, ['downloader' => $downloader]);
        });

        if ($this->app->runningInConsole() && ! $this->app->runningUnitTests()) {
            $this->app->extend(Downloader::class, function (Downloader $downloader) {
                return new ConsoleDownloader($downloader, OutputFactory::make());
            });
        }
    }

    /**
     * Register any package file reader.
     */
    protected function registerFileReader(): void
    {
        $this->app->bind(FileReader::class, BaseFileReader::class);
    }

    /**
     * Register any package resource parser.
     */
    protected function registerParser(): void
    {
        $this->app->bind(Parser::class, FileParser::class);

        if ($this->app->runningInConsole() && ! $this->app->runningUnitTests()) {
            $this->app->extend(Parser::class, function (Parser $parser) {
                return new ProgressParser($parser, OutputFactory::make());
            });
        }
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
