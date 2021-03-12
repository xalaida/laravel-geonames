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
use Nevadskiy\Geonames\Services\DownloadService;
use Nevadskiy\Geonames\Services\SupplyService;
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
     * The dispatcher instance.
     *
     * @var Dispatcher
     */
    protected $dispatcher;

    /**
     * The download service instance.
     *
     * @var DownloadService
     */
    protected $downloadService;

    /**
     * The supply service instance.
     *
     * @var SupplyService
     */
    protected $supplyService;

    /**
     * The alternate name parser instance.
     *
     * @var AlternateNameParser
     */
    protected $alternateNameParser;

    /**
     * The translation supplier instance.
     *
     * @var TranslationSupplier
     */
    protected $translationSupplier;

    /**
     * Execute the console command.
     */
    public function handle(
        Geonames $geonames,
        Dispatcher $dispatcher,
        DownloadService $downloadService,
        SupplyService $supplyService,
        AlternateNameParser $alternateNameParser,
        TranslationSupplier $translationSupplier
    ): void
    {
        $this->init($geonames, $dispatcher, $downloadService, $supplyService, $alternateNameParser, $translationSupplier);
        $this->setUpDownloader($this->downloadService->getDownloader());

        $this->info('Start inserting geonames dataset.');
        $this->dispatcher->dispatch(new GeonamesCommandReady());

        $this->insert();
        // $this->translate();

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
        Dispatcher $dispatcher,
        DownloadService $downloadService,
        SupplyService $supplyService,
        AlternateNameParser $alternateNameParser,
        TranslationSupplier $translationSupplier
    ): void
    {
        $this->geonames = $geonames;
        $this->dispatcher = $dispatcher;
        $this->downloadService = $downloadService;
        $this->supplyService = $supplyService;
        $this->alternateNameParser = $alternateNameParser;
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
     */
    private function insert(): void
    {
        $this->truncateAttempt();

        $this->setUpProgressBar();

        if ($this->geonames->shouldSupplyCountries()) {
            $this->supplyService->addCountryInfo($this->downloadService->downloadCountryInfoFile());
        }

        foreach ($this->downloadService->downloadSourceFiles() as $path) {
            $this->info("Processing the {$path} file.");
            $this->supplyService->insert($path);
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

        $this->supplyService->getGeonamesParser()
            ->enableCountingLines()
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
