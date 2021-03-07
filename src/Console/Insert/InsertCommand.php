<?php

namespace Nevadskiy\Geonames\Console\Insert;

use Illuminate\Console\Command;
use Illuminate\Console\ConfirmableTrait;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\Facades\DB;
use Nevadskiy\Geonames\Events\GeonamesCommandReady;
use Nevadskiy\Geonames\Models\City;
use Nevadskiy\Geonames\Models\Continent;
use Nevadskiy\Geonames\Models\Country;
use Nevadskiy\Geonames\Models\Division;
use Nevadskiy\Geonames\Parsers\CountryInfoParser;
use Nevadskiy\Geonames\Parsers\GeonamesParser;
use Nevadskiy\Geonames\Suppliers\CitySupplier;
use Nevadskiy\Geonames\Suppliers\ContinentSupplier;
use Nevadskiy\Geonames\Suppliers\CountrySupplier;
use Nevadskiy\Geonames\Suppliers\DivisionSupplier;
use Nevadskiy\Geonames\Support\Downloader\ConsoleDownloader;

class InsertCommand extends Command
{
    use ConfirmableTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'geonames:insert {--truncate} {--update-files}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Insert geonames dataset in the database.';

    /**
     * The downloader instance.
     *
     * @var ConsoleDownloader
     */
    protected $downloader;

    /**
     * The dispatcher instance.
     *
     * @var Dispatcher
     */
    protected $dispatcher;

    /**
     * The geonames parser instance.
     *
     * @var GeonamesParser
     */
    protected $geonamesParser;

    /**
     * The geonames country info parser instance.
     *
     * @var CountryInfoParser
     */
    protected $countryInfoParser;

    /**
     * The continent supplier instance.
     *
     * @var ContinentSupplier
     */
    protected $continentSupplier;

    /**
     * The country supplier instance.
     *
     * @var CountrySupplier
     */
    protected $countrySupplier;

    /**
     * The division supplier instance.
     *
     * @var DivisionSupplier
     */
    protected $divisionSupplier;

    /**
     * The city supplier instance.
     *
     * @var CitySupplier
     */
    protected $citySupplier;

    /**
     * Execute the console command.
     */
    public function handle(
        ConsoleDownloader $downloader,
        Dispatcher $dispatcher,
        GeonamesParser $geonamesParser,
        CountryInfoParser $countryInfoParser,
        ContinentSupplier $continentSupplier,
        CountrySupplier $countrySupplier,
        DivisionSupplier $divisionSupplier,
        CitySupplier $citySupplier
    ): void
    {
        $this->init($downloader, $dispatcher, $geonamesParser, $countryInfoParser, $continentSupplier, $countrySupplier, $divisionSupplier, $citySupplier);

        $this->info('Start inserting geonames dataset.');
        $this->dispatcher->dispatch(new GeonamesCommandReady());

        $this->truncateAttempt();
        $this->insert();

        $this->info('Geonames dataset has been successfully inserted.');
    }

    /**
     * Truncate a table if the option is specified.
     */
    protected function truncateAttempt(): void
    {
        if (! $this->confirmToProceed()) {
            return;
        }

        if (! $this->option('truncate')) {
            return;
        }

        $this->performTruncate();
    }

    /**
     * Truncate a table.
     */
    private function performTruncate(): void
    {
        foreach ([Continent::TABLE, Country::TABLE, Division::TABLE, City::TABLE] as $table) {
            DB::table($table)->truncate();
            $this->info("Table {$table} has been truncated.");
        }
    }

    /**
     * Init the command instance with all required services.
     */
    private function init(
        ConsoleDownloader $downloader,
        Dispatcher $dispatcher,
        GeonamesParser $geonamesParser,
        CountryInfoParser $countryInfoParser,
        ContinentSupplier $continentSupplier,
        CountrySupplier $countrySupplier,
        DivisionSupplier $divisionSupplier,
        CitySupplier $citySupplier
    ): void
    {
        $this->downloader = $this->setUpDownloader($downloader);
        $this->dispatcher = $dispatcher;
        $this->geonamesParser = $geonamesParser;
        $this->continentSupplier = $continentSupplier;
        $this->countryInfoParser = $countryInfoParser;
        $this->countrySupplier = $countrySupplier;
        $this->divisionSupplier = $divisionSupplier;
        $this->citySupplier = $citySupplier;
    }

    /**
     * Set up the console downloader.
     *
     * @param ConsoleDownloader $downloader
     */
    private function setUpDownloader(ConsoleDownloader $downloader): ConsoleDownloader
    {
        $downloader->withProgressBar($this->getOutput());

        if ($this->option('update-files')) {
            $downloader->update();
        }

        return $downloader;
    }

    /**
     * Insert the geonames dataset.
     */
    private function insert(): void
    {
        // TODO: refactor.

        $geonamesPath = $this->downloadGeonamesFile();

        $this->setUpProgressBar();

        $this->info('Start processing continents');
        $this->continentSupplier->init();
        foreach ($this->geonamesParser->forEach($geonamesPath) as $id => $data) {
            $this->continentSupplier->insert($id, $data);
        }

        $this->info('Start processing countries');
        $this->countrySupplier->setCountryInfos($this->countryInfoParser->all($this->downloadCountryInfoFile()));
        $this->countrySupplier->init();
        foreach ($this->geonamesParser->forEach($geonamesPath) as $id => $data) {
            $this->countrySupplier->insert($id, $data);
        }

        $this->info('Start processing divisions');
        $this->divisionSupplier->init();
        foreach ($this->geonamesParser->forEach($geonamesPath) as $id => $data) {
            $this->divisionSupplier->insert($id, $data);
        }

        $this->info('Start processing cities');
        $this->citySupplier->init();
        foreach ($this->geonamesParser->forEach($geonamesPath) as $id => $data) {
            $this->citySupplier->insert($id, $data);
        }
    }

    /**
     * Set up the progress bar.
     */
    private function setUpProgressBar(int $step = 1000): void
    {
        $progress = $this->output->createProgressBar();

        $this->geonamesParser->enableCountingLines()
            ->onReady(static function (int $linesCount) use ($progress) {
                $progress->start($linesCount);
            })
            ->onEach(static function () use ($progress, $step) {
                $progress->advance($step);
            }, $step)
            ->onFinish(function () use ($progress) {
                $progress->finish();
                $this->output->newLine();
            });
    }

    /**
     * Download geonames' geonames file.
     *
     * @return string|array
     */
    private function downloadGeonamesFile()
    {
        return $this->downloader->download($this->getGeonamesUrl(), config('geonames.directory'));
    }

    /**
     * Get the URL of the geonames' main file.
     *
     * @return string
     */
    private function getGeonamesUrl(): string
    {
        return "http://download.geonames.org/export/dump/allCountries.zip";
    }

    /**
     * Download geonames' country info file.
     *
     * @return string
     */
    private function downloadCountryInfoFile(): string
    {
        return $this->downloader->download($this->getCountryInfoUrl(), config('geonames.directory'));
    }

    /**
     * Get the URL of the geonames' country info file.
     *
     * @return string
     */
    private function getCountryInfoUrl(): string
    {
        return "http://download.geonames.org/export/dump/countryInfo.txt";
    }

    // TODO: add concrete sources to insert from.
//    /**
//     * Get cities source path.
//     */
//    protected function getCitiesSourcePath(): string
//    {
//        if ($this->hasOptionSourcePath()) {
//            return $this->getOptionSourcePath();
//        }
//
//        return config('geonames.directory') . DIRECTORY_SEPARATOR . config('geonames.files.all_countries');
//    }
//
//    /**
//     * Determine whether the command has given source option.
//     *
//     * @return bool
//     */
//    protected function hasOptionSourcePath(): bool
//    {
//        return (bool) $this->option('source');
//    }
//
//    /**
//     * Get source path from the command option.
//     *
//     * @return string
//     */
//    protected function getOptionSourcePath(): string
//    {
//        $path = base_path($this->option('source'));
//
//        if (! file_exists($path)) {
//            throw new RuntimeException("File does not exist {$path}.");
//        }
//
//        return $path;
//    }
}
