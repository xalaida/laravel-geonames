<?php

namespace Nevadskiy\Geonames\Console\Update;

use Illuminate\Console\Command;
use Illuminate\Contracts\Events\Dispatcher;
use Nevadskiy\Geonames\Console\Traits\CleanFolder;
use Nevadskiy\Geonames\Events\GeonamesCommandReady;
use Nevadskiy\Geonames\Geonames;
use Nevadskiy\Geonames\Services\DownloadService;
use Nevadskiy\Geonames\Services\SupplyService;
use Nevadskiy\Geonames\Support\Cleaner\DirectoryCleaner;

class UpdateCommand extends Command
{
    use CleanFolder;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'geonames:update {--keep-files}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Make a daily update for the geonames database.';

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
     * The directory cleaner instance.
     *
     * @var DirectoryCleaner
     */
    protected $directoryCleaner;

    /**
     * Execute the console command.
     */
    public function handle(
        Geonames $geonames,
        Dispatcher $dispatcher,
        DownloadService $downloadService,
        SupplyService $supplyService,
        DirectoryCleaner $directoryCleaner
    ): void {
        $this->init($geonames, $dispatcher, $downloadService, $supplyService, $directoryCleaner);

        // TODO: check if any items exists in database.

        $this->prepare();
        $this->modify();
        $this->delete();
        $this->updateTranslations();
        $this->cleanFolder();

        $this->info('Daily update had been completed.');
    }

    /**
     * Init the command instance with all required services.
     */
    protected function init(
        Geonames $geonames,
        Dispatcher $dispatcher,
        DownloadService $downloadService,
        SupplyService $supplyService,
        DirectoryCleaner $directoryCleaner
    ): void {
        $this->geonames = $geonames;
        $this->dispatcher = $dispatcher;
        $this->downloadService = $downloadService;
        $this->supplyService = $supplyService;
        $this->directoryCleaner = $directoryCleaner;
    }

    /**
     * Delete items according to the geonames resource.
     */
    protected function modify(): void
    {
        $this->info('Start processing daily modifications.');

        if ($this->geonames->shouldSupplyCountries()) {
            $this->supplyService->addCountryInfo($this->downloadService->downloadCountryInfoFile());
        }

        $this->supplyService->modify($this->downloadService->downloadDailyModifications());
    }

    /**
     * Delete items according to the geonames resource.
     */
    protected function delete(): void
    {
        $this->info('Start processing daily deletes.');
        $this->supplyService->delete($this->downloadService->downloadDailyDeletes());
    }

    /**
     * Update geonames translations.
     */
    protected function updateTranslations(): void
    {
        if ($this->geonames->shouldSupplyTranslations()) {
            $this->call('geonames:translations:update', [
                '--keep-files' => true,
            ]);
        }
    }

    /**
     * Prepare the command.
     */
    protected function prepare(): void
    {
        $this->info('Start daily updating.');
        $this->dispatcher->dispatch(new GeonamesCommandReady());
    }
}
