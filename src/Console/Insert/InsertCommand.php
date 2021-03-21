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
use Nevadskiy\Geonames\Support\Cleaner\DirectoryCleaner;

class InsertCommand extends Command
{
    use ConfirmableTrait,
        CleanFolder;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'geonames:insert {--reset} {--keep-files}';

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
        TranslateService $translateService,
        DirectoryCleaner $directoryCleaner
    ): void {
        $this->init($geonames, $dispatcher, $downloadService, $supplyService, $translateService, $directoryCleaner);

        $this->info('Start inserting the geonames database.');

        $this->prepare();
        $this->insert();
        $this->translate();
        $this->cleanFolder();

        $this->info('The geonames dataset has been inserted.');
    }

    /**
     * Init the command instance with all required services.
     */
    private function init(
        Geonames $geonames,
        Dispatcher $dispatcher,
        DownloadService $downloadService,
        SupplyService $supplyService,
        TranslateService $translateService,
        DirectoryCleaner $directoryCleaner
    ): void {
        $this->geonames = $geonames;
        $this->dispatcher = $dispatcher;
        $this->downloadService = $downloadService;
        $this->supplyService = $supplyService;
        $this->translateService = $translateService;
        $this->directoryCleaner = $directoryCleaner;
    }

    /**
     * Insert the geonames dataset.
     */
    private function insert(): void
    {
        $this->reset();

        if ($this->geonames->shouldSupplyCountries()) {
            $this->supplyService->addCountryInfo($this->downloadService->downloadCountryInfoFile());
        }

        foreach ($this->downloadService->downloadSourceFiles() as $path) {
            $this->info("Start inserting from file {$path}.");
            $this->supplyService->insert($path);
        }
    }

    /**
     * Translate inserted data.
     */
    private function translate(): void
    {
        if ($this->geonames->shouldSupplyTranslations()) {
            $this->call('geonames:translations:insert', [
                '--reset' => $this->option('reset'),
                '--keep-files' => true,
            ]);
        }
    }

    /**
     * Reset tables if the option is specified.
     */
    protected function reset(): void
    {
        if (! $this->option('reset')) {
            return;
        }

        $tables = $this->getTables();

        if (! $this->confirmToProceed($this->getResetWarning($tables))) {
            return;
        }

        $this->performReset($tables);
    }

    /**
     * Get the reset warning message.
     */
    private function getResetWarning(array $tables): string
    {
        return sprintf('The following tables will be truncated: %s', implode(', ', $tables));
    }

    /**
     * Reset geonames tables.
     */
    private function performReset(array $tables): void
    {
        foreach ($tables as $table) {
            DB::table($table)->truncate();
            $this->info("Table {$table} has been truncated.");
        }
    }

    /**
     * Get the tables.
     */
    private function getTables(): array
    {
        return collect($this->geonames->modelClasses())
            ->map(function (string $class) {
                return (new $class())->getTable();
            })
            ->toArray();
    }

    /**
     * Prepare the command.
     */
    private function prepare(): void
    {
        $this->dispatcher->dispatch(new GeonamesCommandReady());
    }
}
