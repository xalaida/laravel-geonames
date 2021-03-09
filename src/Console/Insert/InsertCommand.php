<?php

namespace Nevadskiy\Geonames\Console\Insert;

use Illuminate\Console\Command;
use Illuminate\Console\ConfirmableTrait;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\Facades\DB;
use Nevadskiy\Geonames\Events\GeonamesCommandReady;
use Nevadskiy\Geonames\Geonames;
use Nevadskiy\Geonames\Parsers\CountryInfoParser;
use Nevadskiy\Geonames\Parsers\GeonamesParser;
use Nevadskiy\Geonames\Services\DownloadService;
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
     * The geonames instance.
     *
     * @var Geonames
     */
    protected $geonames;

    /**
     * The download service instance.
     *
     * @var DownloadService
     */
    protected $downloadService;

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
        Geonames $geonames,
        DownloadService $downloadService,
        Dispatcher $dispatcher,
        GeonamesParser $geonamesParser,
        CountryInfoParser $countryInfoParser,
        ContinentSupplier $continentSupplier,
        CountrySupplier $countrySupplier,
        DivisionSupplier $divisionSupplier,
        CitySupplier $citySupplier
    ): void
    {
        $this->init($geonames, $downloadService, $dispatcher, $geonamesParser, $countryInfoParser, $continentSupplier, $countrySupplier, $divisionSupplier, $citySupplier);

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
        foreach ($this->geonames->supply() as $table) {
            DB::table($table)->truncate();
            $this->info("Table {$table} has been truncated.");
        }
    }

    /**
     * Init the command instance with all required services.
     */
    private function init(
        Geonames $geonames,
        DownloadService $downloadService,
        Dispatcher $dispatcher,
        GeonamesParser $geonamesParser,
        CountryInfoParser $countryInfoParser,
        ContinentSupplier $continentSupplier,
        CountrySupplier $countrySupplier,
        DivisionSupplier $divisionSupplier,
        CitySupplier $citySupplier
    ): void
    {
        $this->geonames = $geonames;
        $this->downloadService = $downloadService;
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
    private function setUpDownloader(ConsoleDownloader $downloader): void
    {
        $downloader->withProgressBar($this->getOutput());

        if ($this->option('update-files')) {
            $downloader->update();
        }
    }

    /**
     * Insert the geonames dataset.
     * TODO: refactor
     */
    private function insert(): void
    {
        $this->setUpDownloader($this->downloadService->getDownloader());

        $this->setUpProgressBar();

        if ($this->geonames->shouldSupplyCountries()) {
            $this->countrySupplier->setCountryInfos(
                $this->countryInfoParser->all($this->downloadService->downloadCountryInfoFile())
            );
        }

        foreach ($this->downloadService->downloadGeonamesFiles() as $path) {
            $this->insertFromSource($path);
        }
    }

    /**
     * Insert dataset from the given source path.
     *
     * @param string $sourcePath
     */
    public function insertFromSource(string $sourcePath): void
    {
        $this->info("Processing {$sourcePath} file.");

        if ($this->geonames->shouldSupplyContinents()) {
            $this->info('Start processing continents');
            $this->continentSupplier->init();
            foreach ($this->geonamesParser->forEach($sourcePath) as $id => $data) {
                $this->continentSupplier->insert($id, $data);
            }
            $this->continentSupplier->commit();
        }

        if ($this->geonames->shouldSupplyCountries()) {
            $this->info('Start processing countries');
            $this->countrySupplier->setCountryInfos(
                $this->countryInfoParser->all($this->downloadService->downloadCountryInfoFile())
            );
            $this->countrySupplier->init();
            foreach ($this->geonamesParser->forEach($sourcePath) as $id => $data) {
                $this->countrySupplier->insert($id, $data);
            }
            $this->countrySupplier->commit();
        }

        if ($this->geonames->shouldSupplyDivisions()) {
            $this->info('Start processing divisions');
            $this->divisionSupplier->init();
            foreach ($this->geonamesParser->forEach($sourcePath) as $id => $data) {
                $this->divisionSupplier->insert($id, $data);
            }
            $this->divisionSupplier->commit();
        }

        if ($this->geonames->shouldSupplyCities()) {
            $this->info('Start processing cities');
            $this->citySupplier->init();
            foreach ($this->geonamesParser->forEach($sourcePath) as $id => $data) {
                $this->citySupplier->insert($id, $data);
            }
            $this->citySupplier->commit();
        }
    }

    /**
     * Set up the progress bar.
     * TODO: refactor using decorator pattern
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
}
