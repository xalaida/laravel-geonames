<?php

namespace Nevadskiy\Geonames\Console\Insert;

use Illuminate\Console\Command;
use Illuminate\Console\ConfirmableTrait;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Database\Eloquent\Model;
use Nevadskiy\Geonames\Console\Traits\CleanFolder;
use Nevadskiy\Geonames\Events\GeonamesCommandReady;
use Nevadskiy\Geonames\Geonames;
use Nevadskiy\Geonames\Services\DownloadService;
use Nevadskiy\Geonames\Services\TranslateService;
use Nevadskiy\Geonames\Support\Cleaner\DirectoryCleaner;
use Nevadskiy\Translatable\Models\Translation;

class InsertTranslationsCommand extends Command
{
    use ConfirmableTrait,
        CleanFolder;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'geonames:translations:insert {--reset} {--keep-files}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Seed geonames translations into the database.';

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

        $this->info('Start inserting translations. It may take some time.');

        $this->prepare();
        $this->reset();
        $this->insert();
        $this->cleanFolder();

        $this->info('Translations have been successfully inserted.');
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
     * Insert the geonames dataset.
     */
    protected function reset(): void
    {
        if (! $this->option('reset')) {
            return;
        }

        if (! $this->confirmToProceed('Translations of geonames models will be deleted.')) {
            return;
        }

        $this->performReset();
    }

    /**
     * Insert the geonames alternate names dataset.
     */
    protected function insert(): void
    {
        foreach ($this->downloadService->downloaderAlternateNames() as $path) {
            $this->info("Start translating from file {$path}.");
            $this->translateService->insert($path);
        }
    }

    /**
     * Reset the current translations.
     */
    protected function performReset(): void
    {
        foreach ($this->geonames->modelClasses() as $model) {
            $this->deleteTranslations(new $model());
        }

        $this->info('Translations have been reset.');
    }

    /**
     * Delete translations for the given model.
     *
     * @param $model
     */
    protected function deleteTranslations(Model $model): void
    {
        Translation::query()
            ->where('translatable_type', $model->getMorphClass())
            ->delete();
    }

    /**
     * Prepare the command.
     */
    protected function prepare(): void
    {
        $this->dispatcher->dispatch(new GeonamesCommandReady());
    }
}
