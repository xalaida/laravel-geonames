<?php

namespace Nevadskiy\Geonames\Console\Update;

use Illuminate\Console\Command;
use Illuminate\Contracts\Events\Dispatcher;
use Nevadskiy\Geonames\Console\Traits\CleanFolder;
use Nevadskiy\Geonames\Events\GeonamesCommandReady;
use Nevadskiy\Geonames\Geonames;
use Nevadskiy\Geonames\Services\DownloadService;
use Nevadskiy\Geonames\Services\TranslateService;
use Nevadskiy\Geonames\Support\Cleaner\DirectoryCleaner;

class UpdateTranslationsCommand extends Command
{
    use CleanFolder;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'geonames:translations:update {--keep-files}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Make a daily update for the geonames translations.';

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
     * The translate service instance.
     *
     * @var TranslateService
     */
    protected $translateService;

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
        TranslateService $translateService,
        DirectoryCleaner $directoryCleaner
    ): void {
        $this->init($geonames, $dispatcher, $downloadService, $translateService, $directoryCleaner);

        $this->prepare();
        $this->modify();
        $this->delete();
        $this->cleanFolder();

        $this->info('Translations update has been completed.');
    }

    /**
     * Init the command instance with all required services.
     */
    protected function init(
        Geonames $geonames,
        Dispatcher $dispatcher,
        DownloadService $downloadService,
        TranslateService $translateService,
        DirectoryCleaner $directoryCleaner
    ): void {
        $this->geonames = $geonames;
        $this->dispatcher = $dispatcher;
        $this->downloadService = $downloadService;
        $this->translateService = $translateService;
        $this->directoryCleaner = $directoryCleaner;
    }

    /**
     * Modify translations according to the geonames resource.
     */
    protected function modify(): void
    {
        $this->info('Start processing alternate names daily modifications.');
        $this->translateService->modify($this->downloadService->downloadDailyAlternateNamesModifications());
    }

    /**
     * Delete translations according to the geonames resource.
     */
    protected function delete(): void
    {
        $this->info('Start processing alternate names daily deletes.');
        $this->translateService->delete($this->downloadService->downloadDailyAlternateNamesDeletes());
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
