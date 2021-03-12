<?php

namespace Nevadskiy\Geonames\Console\Insert;

use Illuminate\Console\Command;
use Illuminate\Console\ConfirmableTrait;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\Facades\DB;
use Nevadskiy\Geonames\Events\GeonamesCommandReady;
use Nevadskiy\Geonames\Geonames;
use Nevadskiy\Geonames\Services\DownloadService;
use Nevadskiy\Geonames\Services\SupplyService;
use Nevadskiy\Geonames\Services\TranslateService;
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
     * The translate service instance.
     *
     * @var TranslateService
     */
    protected $translateService;

    /**
     * Execute the console command.
     */
    public function handle(
        Geonames $geonames,
        Dispatcher $dispatcher,
        DownloadService $downloadService,
        SupplyService $supplyService,
        TranslateService $translateService
    ): void
    {
        $this->init($geonames, $dispatcher, $downloadService, $supplyService, $translateService);

        $this->info('Start inserting geonames dataset.');
        $this->dispatcher->dispatch(new GeonamesCommandReady());

        $this->insert();
        $this->translate();

        $this->info('Geonames dataset has been successfully inserted.');
    }

    /**
     * Truncate a table if the option is specified.
     */
    protected function truncate(): void
    {
        if (! $this->confirmToProceed($this->getTruncateWarning())) {
            return;
        }

        if (! $this->option('truncate')) {
            return;
        }

        $this->performTruncate();
    }

    /**
     * Get the truncate warning message.
     *
     * @return string
     */
    private function getTruncateWarning(): string
    {
        return sprintf('The following tables will be truncated: %s', implode(', ', $this->geonames->supply()));
    }

    /**
     * Truncate geonames tables.
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
        TranslateService $translateService
    ): void
    {
        $this->geonames = $geonames;
        $this->dispatcher = $dispatcher;
        $this->downloadService = $downloadService;
        $this->supplyService = $supplyService;
        $this->translateService = $translateService;

        $this->setUpDownloader($this->downloadService->getDownloader());
    }

    /**
     * Set up the console downloader.
     *
     * @param ConsoleDownloader|Downloader $downloader
     */
    private function setUpDownloader(ConsoleDownloader $downloader): void
    {
        // TODO: probably resolve output directly from container
        //  $output = new Symfony\Component\Console\Output\ConsoleOutput();
        // $output->writeln("<info>my message</info>");

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
        $this->setUpProgressBar();

        $this->truncate();

        if ($this->geonames->shouldSupplyCountries()) {
            $this->supplyService->addCountryInfo($this->downloadService->downloadCountryInfoFile());
        }

        foreach ($this->downloadService->downloadSourceFiles() as $path) {
            $this->info("Processing the {$path} file.");
            $this->supplyService->insert($path);
        }
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

        $this->translateService->insert($this->downloadService->downloaderAlternateNames());

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

        $this->translateService->getAlternateNameParser()
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
}
