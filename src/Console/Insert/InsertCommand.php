<?php

namespace Nevadskiy\Geonames\Console\Insert;

use Illuminate\Console\Command;
use Illuminate\Console\ConfirmableTrait;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\Facades\DB;
use Nevadskiy\Geonames\Console\Traits\CleanFolder;
use Nevadskiy\Geonames\Events\GeonamesCommandReady;
use Nevadskiy\Geonames\Geonames;
use Nevadskiy\Geonames\Services\DownloadService;
use Nevadskiy\Geonames\Services\SupplyService;
use Nevadskiy\Geonames\Services\TranslateService;
use Nevadskiy\Geonames\Support\Downloader\Downloader;

class InsertCommand extends Command
{
    use ConfirmableTrait,
        CleanFolder;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'geonames:insert {--truncate} {--keep-files} {--update-files} {--without-translations}';

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

        $this->prepare();
        $this->insert();
        $this->translate();

        $this->info('Geonames dataset has been inserted.');
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
    }

    /**
     * Insert the geonames dataset.
     */
    private function insert(): void
    {
        $this->truncate();

        if ($this->geonames->shouldSupplyCountries()) {
            $this->info('Add country info.');
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
        if ($this->option('without-translations')) {
            return;
        }

        $this->call('geonames:translations', [
            '--reset' => $this->option('truncate'),
            '--update-files' => $this->option('update-files'),
        ]);

        $this->cleanFolder();
    }

    /**
     * Truncate a table if the option is specified.
     */
    protected function truncate(): void
    {
        if (! $this->option('truncate')) {
            return;
        }

        if (! $this->confirmToProceed($this->getTruncateWarning())) {
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
     * Prepare the command.
     */
    private function prepare(): void
    {
        $this->dispatcher->dispatch(new GeonamesCommandReady());
        $this->setUpDownloader($this->downloadService->getDownloader());
    }

    /**
     * Set up the console downloader.
     *
     * @param Downloader $downloader
     */
    private function setUpDownloader(Downloader $downloader): void
    {
        if ($this->option('update-files')) {
            $downloader->update();
        }
    }
}
