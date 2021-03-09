<?php

namespace Nevadskiy\Geonames\Console\Insert;

use Generator;
use Illuminate\Console\Command;
use Illuminate\Console\ConfirmableTrait;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\Facades\DB;
use Nevadskiy\Geonames\Events\GeonamesCommandReady;
use Nevadskiy\Geonames\Geonames;
use Nevadskiy\Geonames\Parsers\AlternateNameParser;
use Nevadskiy\Geonames\Parsers\CountryInfoParser;
use Nevadskiy\Geonames\Parsers\GeonamesParser;
use Nevadskiy\Geonames\Services\DownloadService;
use Nevadskiy\Geonames\Suppliers\CitySupplier;
use Nevadskiy\Geonames\Suppliers\ContinentSupplier;
use Nevadskiy\Geonames\Suppliers\CountrySupplier;
use Nevadskiy\Geonames\Suppliers\DivisionSupplier;
use Nevadskiy\Geonames\Suppliers\Translations\TranslationSupplier;
use Nevadskiy\Geonames\Support\Downloader\ConsoleDownloader;
use Nevadskiy\Geonames\Support\Downloader\Downloader;

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
     * The alternate name parser instance.
     *
     * @var AlternateNameParser
     */
    protected $alternateNameParser;

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
     * The translation supplier instance.
     *
     * @var CitySupplier
     */
    protected $translationSupplier;


    /**
     * Execute the console command.
     */
    public function handle(
        Geonames $geonames,
        DownloadService $downloadService,
        Dispatcher $dispatcher,
        GeonamesParser $geonamesParser,
        CountryInfoParser $countryInfoParser,
        AlternateNameParser $alternateNameParser,
        ContinentSupplier $continentSupplier,
        CountrySupplier $countrySupplier,
        DivisionSupplier $divisionSupplier,
        CitySupplier $citySupplier,
        TranslationSupplier $translationSupplier
    ): void
    {
        $this->init($geonames, $downloadService, $dispatcher, $geonamesParser, $countryInfoParser, $alternateNameParser, $continentSupplier, $countrySupplier, $divisionSupplier, $citySupplier, $translationSupplier);
        $this->setUpDownloader($this->downloadService->getDownloader());

        $this->info('Start inserting geonames dataset.');
        $this->dispatcher->dispatch(new GeonamesCommandReady());

        // $this->insert();
        $this->translate();

        $this->info('Geonames dataset has been successfully inserted.');
    }

    /**
     * Truncate a table if the option is specified.
     */
    protected function truncateAttempt(): void
    {
        // TODO: add warning message with tables that are going to be truncated
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
        AlternateNameParser $alternateNameParser,
        ContinentSupplier $continentSupplier,
        CountrySupplier $countrySupplier,
        DivisionSupplier $divisionSupplier,
        CitySupplier $citySupplier,
        TranslationSupplier $translationSupplier
    ): void
    {
        $this->geonames = $geonames;
        $this->downloadService = $downloadService;
        $this->dispatcher = $dispatcher;
        $this->geonamesParser = $geonamesParser;
        $this->continentSupplier = $continentSupplier;
        $this->countryInfoParser = $countryInfoParser;
        $this->alternateNameParser = $alternateNameParser;
        $this->countrySupplier = $countrySupplier;
        $this->divisionSupplier = $divisionSupplier;
        $this->citySupplier = $citySupplier;
        $this->translationSupplier = $translationSupplier;
    }

    /**
     * Set up the console downloader.
     *
     * @param ConsoleDownloader|Downloader $downloader
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
        $this->truncateAttempt();

        $this->setUpProgressBar();

        if ($this->geonames->shouldSupplyCountries()) {
            $this->countrySupplier->setCountryInfos(
                $this->countryInfoParser->all($this->downloadService->downloadCountryInfoFile())
            );
        }

        foreach ($this->downloadService->downloadSourceFiles() as $path) {
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

        // TODO: probably use progress format
        // if ($linesCount) {
        // $this->progress->setFormat("<info>Downloading:</info> {$url}\n%bar% %percent%%\n<info>Remaining Time:</info> %remaining%");
        // }

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
     * Translate inserted data.
     */
    private function translate(): void
    {
        $this->info('Start seeding translations. It may take some time.');

        // TODO: remove
        DB::table('translations')->truncate();

        $this->setUpTranslationsProgressBar();

        foreach ($this->translations() as $id => $translation) {
            $this->translationSupplier->insert($id, $translation);
        }

        $this->info('Translations have been successfully seeded.');
    }

    /**
     * TODO: extract into parser decorator ProgressBarParser.php
     * Set up the progress bar.
     */
    private function setUpTranslationsProgressBar(int $step = 1000): void
    {
        $progress = $this->output->createProgressBar();
        $progress->setFormat('very_verbose');

        $this->alternateNameParser->enableCountingLines()
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
     * Get translations for seeding.
     */
    private function translations(): Generator
    {
        return $this->alternateNameParser->forEach(
            $this->downloadService->downloaderAlternateNames()
        );
    }
}
