<?php

namespace Nevadskiy\Geonames\Console\Insert;

use Illuminate\Console\Command;
use Illuminate\Console\ConfirmableTrait;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Nevadskiy\Geonames\Console\Traits\CleanFolder;
use Nevadskiy\Geonames\Events\GeonamesCommandReady;
use Nevadskiy\Geonames\Geonames;
use Nevadskiy\Geonames\Models\City;
use Nevadskiy\Geonames\Models\Continent;
use Nevadskiy\Geonames\Models\Country;
use Nevadskiy\Geonames\Models\Division;
use Nevadskiy\Geonames\Services\DownloadService;
use Nevadskiy\Geonames\Services\TranslateService;
use Nevadskiy\Geonames\Support\Downloader\Downloader;

class InsertTranslationsCommand extends Command
{
    use ConfirmableTrait,
        CleanFolder;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'geonames:translations {--reset} {--keep-files} {--update-files}';

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
     * Execute the console command.
     */
    public function handle(
        Geonames $geonames,
        Dispatcher $dispatcher,
        DownloadService $downloadService,
        TranslateService $translateService
    ): void
    {
        $this->init($geonames, $dispatcher, $downloadService, $translateService);

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
    private function init(
        Geonames $geonames,
        Dispatcher $dispatcher,
        DownloadService $downloadService,
        TranslateService $translateService
    ): void
    {
        $this->geonames = $geonames;
        $this->dispatcher = $dispatcher;
        $this->downloadService = $downloadService;
        $this->translateService = $translateService;
    }

    /**
     * Insert the geonames dataset.
     */
    private function reset(): void
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
     * Insert the geonames dataset.
     */
    private function insert(): void
    {
        // TODO: feature downloading specific alternate names source (single country or everything)
        $this->translateService->insert($this->downloadService->downloaderAlternateNames());
    }

    /**
     * Reset the current translations.
     */
    private function performReset(): void
    {
        $models = [
            'continents' => Continent::class,
            'countries' => Country::class,
            'divisions' => Division::class,
            'cities' => City::class,
        ];

        foreach (Arr::only($models, $this->geonames->supply()) as $model) {
            $this->deleteTranslations(new $model);
        }

        $this->info('Translations have been reset.');
    }

    /**
     * Delete translations for the given model.
     *
     * @param $model
     */
    private function deleteTranslations(Model $model): void
    {
        DB::table('translations')
            ->where('translatable_type', $model->getMorphClass())
            ->delete();
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
