<?php

namespace Nevadskiy\Geonames;

use Facade\Ignition\QueryRecorder\QueryRecorder;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\ServiceProvider;
use Laravel\Nova\Nova;
use Nevadskiy\Geonames\Events\GeonamesCommandReady;
use Nevadskiy\Geonames\Listeners\DisableIgnitionBindings;
use Nevadskiy\Geonames\Nova as Resources;
use Nevadskiy\Geonames\Parsers\FileParser;
use Nevadskiy\Geonames\Parsers\Parser;
use Nevadskiy\Geonames\Parsers\ProgressParser;
use Nevadskiy\Geonames\Suppliers\Translations\CompositeTranslationMapper;
use Nevadskiy\Geonames\Suppliers\Translations\TranslationMapper;
use Nevadskiy\Geonames\Support\Downloader\BaseDownloader;
use Nevadskiy\Geonames\Support\Downloader\ConsoleDownloader;
use Nevadskiy\Geonames\Support\Downloader\Downloader;
use Nevadskiy\Geonames\Support\Downloader\UnzipperDownloader;
use Nevadskiy\Geonames\Support\FileReader\BaseFileReader;
use Nevadskiy\Geonames\Support\FileReader\FileReader;
use Nevadskiy\Geonames\Support\Output\OutputFactory;

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
        $this->registerGeonames();
        $this->registerDownloader();
        $this->registerFileReader();
        $this->registerParser();
        $this->registerSuppliers();
        $this->registerTranslationMapper();
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
        $this->bootNovaResources();
        $this->publishConfig();
        $this->publishMigrations();
    }

    /**
     * Register any module configurations.
     */
    private function registerConfig(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/geonames.php', self::PACKAGE);
    }

    /**
     * Register the geonames.
     */
    private function registerGeonames(): void
    {
        $this->app->singleton(Geonames::class);

        $this->app->when(Geonames::class)
            ->needs('$config')
            ->give(function () {
                return $this->app['config']['geonames'];
            });
    }

    /**
     * Register the downloader.
     */
    private function registerDownloader(): void
    {
        $this->app->bind(Downloader::class, BaseDownloader::class);

        $this->app->extend(Downloader::class, function (Downloader $downloader) {
            return $this->app->make(UnzipperDownloader::class, ['downloader' => $downloader]);
        });

        if ($this->app->runningInConsole()) {
            $this->app->extend(Downloader::class, function (Downloader $downloader) {
                return new ConsoleDownloader($downloader, OutputFactory::make());
            });
        }
    }

    /**
     * Register the file reader.
     */
    private function registerFileReader(): void
    {
        $this->app->bind(FileReader::class, BaseFileReader::class);
    }

    /**
     * Register the resource parser.
     */
    private function registerParser(): void
    {
        $this->app->bind(Parser::class, FileParser::class);

        if ($this->app->runningInConsole()) {
            $this->app->extend(Parser::class, function (Parser $parser) {
                return new ProgressParser($parser, OutputFactory::make());
            });
        }
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
     * Register the translation mapper.
     */
    private function registerTranslationMapper(): void
    {
        $this->app->bind(TranslationMapper::class, function () {
            $mappers = collect([
                'continents' => Suppliers\Translations\ContinentTranslationMapper::class,
                'countries' => Suppliers\Translations\CountryTranslationMapper::class,
                'divisions' => Suppliers\Translations\DivisionTranslationMapper::class,
                'cities' => Suppliers\Translations\CityTranslationMapper::class,
            ])
                ->only($this->app->make(Geonames::class)->supply())
                ->map(function (string $mapper) {
                    return $this->app->make($mapper);
                })
                ->toArray();

            return new CompositeTranslationMapper($mappers);
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
                Console\Insert\InsertCommand::class,
                Console\Insert\InsertTranslationsCommand::class,
                Console\Update\UpdateCommand::class,
            ]);
        }
    }

    /**
     * Boot any module migrations.
     */
    private function bootMigrations(): void
    {
        $geonames = $this->app->make(Geonames::class);

        if ($this->app->runningInConsole() && $geonames->shouldUseDefaultMigrations()) {
            if ($geonames->shouldSupplyContinents()) {
                $this->loadMigrationsFrom(__DIR__.'/../database/migrations/2020_06_06_100000_create_continents_table.php');
            }

            if ($geonames->shouldSupplyCountries()) {
                $this->loadMigrationsFrom(__DIR__.'/../database/migrations/2020_06_06_200000_create_countries_table.php');
            }

            if ($geonames->shouldSupplyDivisions()) {
                $this->loadMigrationsFrom(__DIR__.'/../database/migrations/2020_06_06_300000_create_divisions_table.php');
            }

            if ($geonames->shouldSupplyCities()) {
                $this->loadMigrationsFrom(__DIR__.'/../database/migrations/2020_06_06_400000_create_cities_table.php');
            }
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
     * Boot any module nova resources.
     */
    private function bootNovaResources(): void
    {
        Nova::resources([
            Resources\Continent::class,
            Resources\Country::class,
            Resources\Division::class,
            Resources\City::class,
        ]);
    }

    /**
     * Publish any module configurations.
     */
    private function publishConfig(): void
    {
        $this->publishes([
            __DIR__.'/../config/geonames.php' => config_path('geonames.php'),
        ], self::PACKAGE.'-config');
    }

    /**
     * Publish any module migrations.
     */
    private function publishMigrations(): void
    {
        $this->publishes([
            __DIR__.'/../database/migrations' => database_path('migrations'),
        ], self::PACKAGE.'-migrations');
    }
}
