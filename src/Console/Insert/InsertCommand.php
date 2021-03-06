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
use Nevadskiy\Geonames\Support\FileDownloader\ConsoleFileDownloader;
use Nevadskiy\Geonames\Support\Unzipper\Unzipper;

class InsertCommand extends Command
{
    use ConfirmableTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'geonames:insert {--truncate}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Insert geonames dataset in the database.';

    /**
     * The downloader instance.
     *
     * @var ConsoleFileDownloader
     */
    protected $downloader;

    /**
     * The unzipper instance.
     *
     * @var Unzipper
     */
    protected $unzipper;

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
        ConsoleFileDownloader $downloader,
        Unzipper $unzipper,
        Dispatcher $dispatcher,
        GeonamesParser $geonamesParser,
        CountryInfoParser $countryInfoParser,
        ContinentSupplier $continentSupplier,
        CountrySupplier $countrySupplier,
        DivisionSupplier $divisionSupplier,
        CitySupplier $citySupplier
    ): void
    {
        $this->init($downloader, $unzipper, $dispatcher, $geonamesParser, $countryInfoParser, $continentSupplier, $countrySupplier, $divisionSupplier, $citySupplier);
        $this->setUpDownloader($downloader);

        $this->dispatcher->dispatch(new GeonamesCommandReady());

        $this->info('Start inserting geonames dataset.');

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
        ConsoleFileDownloader $downloader,
        Unzipper $unzipper,
        Dispatcher $dispatcher,
        GeonamesParser $geonamesParser,
        CountryInfoParser $countryInfoParser,
        ContinentSupplier $continentSupplier,
        CountrySupplier $countrySupplier,
        DivisionSupplier $divisionSupplier,
        CitySupplier $citySupplier
    ): void
    {
        $this->downloader = $downloader;
        $this->unzipper = $unzipper;
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
     * TODO: refactor downloader to pass into command already set up (configure in the service provider)
     *
     * @param ConsoleFileDownloader $downloader
     */
    private function setUpDownloader(ConsoleFileDownloader $downloader): void
    {
        $downloader->withProgressBar($this->getOutput())->update();
    }

    /**
     * Modify changed items according to a geonames' resource.
     */
    private function insert(): void
    {
        // TODO: download country info
        $countryInfoPath = $this->downloadCountryInfoFile();
        $this->countrySupplier->setCountryInfos($this->countryInfoParser->all($countryInfoPath));

        // TODO: download specific geonames [ allCountries.zip, US.zip, cities500.zip, etc.]
        $geonamesZipPath = $this->downloadGeonamesFile();

        // TODO: make it works
        // $geonamesPath = $this->unzip($geonamesZipPath);

        // TODO: remove after patching unzipper
        $this->unzip($geonamesZipPath);
        $geonamesPath = storage_path('/meta/geonames/allCountries/allCountries.txt');

        $this->setUpProgressBar();

        $this->info('Start processing continents');
        $this->continentSupplier->init();
        foreach ($this->geonamesParser->forEach($geonamesPath) as $id => $data) {
            $this->continentSupplier->insert($id, $data);
        }

        $this->info('Start processing countries');
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
     * Download geonames' country info file.
     *
     * @return string
     */
    private function downloadCountryInfoFile(): string
    {
        return $this->downloader->download($this->getCountryInfoUrl(), config('geonames.directory'));
    }

    /**
     * Download geonames' geonames file.
     *
     * @return string
     */
    private function downloadGeonamesFile(): string
    {
        return $this->downloader->download($this->getGeonamesUrl(), config('geonames.directory'));
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
     * Unzip a resource by the given path.
     *
     * @param string $path
     */
    private function unzip(string $path): void
    {
        // TODO: refactor unzipper as downloader decorator
        $this->unzipper->extractIntoDirectory()->unzip($path);
    }
}
