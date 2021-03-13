<?php

namespace Nevadskiy\Geonames\Console\Update;

use Illuminate\Console\Command;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\Facades\DB;
use Nevadskiy\Geonames\Events\GeonamesCommandReady;
use Nevadskiy\Geonames\Geonames;
use Nevadskiy\Geonames\Services\DownloadService;
use Nevadskiy\Geonames\Services\SupplyService;
use Nevadskiy\Geonames\Services\TranslateService;

class UpdateCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'geonames:update';

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

        $this->info('Start geonames daily updating.');
        $this->dispatcher->dispatch(new GeonamesCommandReady());

        // TODO: check if any items exists in database.

        DB::beginTransaction();

        $this->modify();
        $this->delete();
        $this->modifyTranslations();
        $this->deleteTranslations();

        DB::rollBack();

        // TODO: delete files

        $this->info('Daily update had been successfully done.');
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
     * Delete items according to the geonames resource.
     */
    private function modify(): void
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
    private function delete(): void
    {
        $this->info('Start processing daily deletes.');
        $this->supplyService->delete($this->downloadService->downloadDailyDeletes());
    }

    /**
     * Modify translations according to the geonames resource.
     */
    private function modifyTranslations(): void
    {
        $this->info('Start processing alternate names daily modifications.');
        $this->translateService->modify($this->downloadService->downloadDailyAlternateNamesModifications());
    }

    /**
     * Delete translations according to the geonames resource.
     */
    private function deleteTranslations(): void
    {
        $this->info('Start processing alternate names daily deletes.');
        $this->translateService->delete($this->downloadService->downloadDailyAlternateNamesDeletes());
    }
}
