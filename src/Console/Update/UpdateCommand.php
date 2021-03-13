<?php

namespace Nevadskiy\Geonames\Console\Update;

use Illuminate\Console\Command;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\Facades\DB;
use Nevadskiy\Geonames\Events\GeonamesCommandReady;
use Nevadskiy\Geonames\Geonames;
use Nevadskiy\Geonames\Services\DownloadService;
use Nevadskiy\Geonames\Services\SupplyService;

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
     * Execute the console command.
     */
    public function handle(
        Geonames $geonames,
        Dispatcher $dispatcher,
        DownloadService $downloadService,
        SupplyService $supplyService
    ): void
    {
        $this->init($geonames, $dispatcher, $downloadService, $supplyService);

        $this->info('Start geonames daily updating.');
        $this->dispatcher->dispatch(new GeonamesCommandReady());

        // TODO: check if items exists in database.

        DB::beginTransaction();

        $this->modify();
        $this->delete();

        DB::rollBack();


        // TODO: process alternate names

        $this->info('Daily update had been successfully done.');
    }

    /**
     * Init the command instance with all required services.
     */
    private function init(
        Geonames $geonames,
        Dispatcher $dispatcher,
        DownloadService $downloadService,
        SupplyService $supplyService
    ): void
    {
        $this->geonames = $geonames;
        $this->dispatcher = $dispatcher;
        $this->downloadService = $downloadService;
        $this->supplyService = $supplyService;
    }

    /**
     * Modify changed items according to a geonames resource.
     */
    private function modify(): void
    {
        $this->info('Start processing modifications.');

        if ($this->geonames->shouldSupplyCountries()) {
            $this->info('Add country info.');
            $this->supplyService->addCountryInfo($this->downloadService->downloadCountryInfoFile());
        }

        $this->supplyService->modify($this->downloadService->downloadDailyModifications());

        // TODO: delete modifications file.
    }

    /**
     * Delete removed items according to a geonames resource.
     */
    private function delete(): void
    {
        $this->info('Start processing deletes.');

        $this->supplyService->delete($this->downloadService->downloadDailyDeletes());

        // TODO: delete deletes file.
    }
}
