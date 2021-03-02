<?php

namespace Nevadskiy\Geonames\Console\Update;

use Carbon\Carbon;
use Carbon\CarbonTimeZone;
use DateTimeInterface;
use Illuminate\Console\Command;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\Facades\DB;
use Nevadskiy\Geonames\Events\GeonamesCommandReady;
use Nevadskiy\Geonames\Parsers\GeonamesParser;
use Nevadskiy\Geonames\Suppliers\CitySupplier;
use Nevadskiy\Geonames\Support\FileDownloader\ConsoleFileDownloader;

class DailyUpdateCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'geonames:update:daily';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Make a daily update for the geonames database.';

    /**
     * The downloader instance.
     *
     * @var ConsoleFileDownloader
     */
    protected $downloader;

    /**
     * The dispatcher instance.
     *
     * @var Dispatcher
     */
    protected $dispatcher;

    /**
     * The geonames parser instance.
     *
     * @var GeonamesParser
     */
    protected $geonamesParser;

    /**
     * The city supplier instance.
     *
     * @var CitySupplier
     */
    protected $citySupplier;

    /**
     * Execute the console command.
     */
    public function handle(
        ConsoleFileDownloader $downloader,
        Dispatcher $dispatcher,
        GeonamesParser $geonamesParser,
        CitySupplier $citySupplier
    ): void
    {
        $this->init($downloader, $dispatcher, $geonamesParser, $citySupplier);
        // TODO: refactor with service container configure
        $this->setUpDownloader($downloader);

        $this->dispatcher->dispatch(new GeonamesCommandReady());

        $this->info('Start geonames daily updating.');


        $date = $this->getPreviousDate();

        // TODO: remove the line when code will be ready
        DB::beginTransaction();

        $this->modify($date);
        $this->delete($date);

        // TODO: remove the line when code will be ready
        DB::rollBack();

        $this->info('Daily update had been successfully done.');
    }

    /**
     * Init the command instance with all required services.
     *
     * @param ConsoleFileDownloader $downloader
     * @param Dispatcher $dispatcher
     * @param GeonamesParser $geonamesParser
     * @param CitySupplier $citySupplier
     */
    private function init(
        ConsoleFileDownloader $downloader,
        Dispatcher $dispatcher,
        GeonamesParser $geonamesParser,
        CitySupplier $citySupplier
    ): void
    {
        $this->downloader = $downloader;
        $this->dispatcher = $dispatcher;
        $this->geonamesParser = $geonamesParser;
        $this->citySupplier = $citySupplier;
    }

    /**
     * Set up the console downloader.
     * TODO: refactor downloader to pass into command already set up (configure in the service provider)
     *
     * @param ConsoleFileDownloader $downloader
     */
    private function setUpDownloader(ConsoleFileDownloader $downloader): void
    {
        $downloader->enableProgressBar($this->getOutput())->update();
    }

    /**
     * Get the previous date of geonames' resources.
     */
    private function getPreviousDate(): Carbon
    {
        return Carbon::yesterday('UTC');
    }

    /**
     * Modify changed items according to a geonames' resource.
     */
    private function modify(DateTimeInterface $previousDate): void
    {
        $modificationsPath = $this->downloadModifications($previousDate);

        foreach ($this->geonamesParser->forEach($modificationsPath) as $id => $data) {
            $this->citySupplier->modify($data, $id);
            // TODO: add other suppliers... and complete them with update method...
        }

        // TODO: delete modifications file.
    }

    /**
     * Delete removed items according to a geonames' resource.
     */
    private function delete(DateTimeInterface $previousDate): void
    {
        $deletesPath = $this->downloadDeletes($previousDate);

        foreach ($this->geonamesParser->forEach($deletesPath) as $id => $data) {
            $this->citySupplier->delete($data, $id);
            // TODO: add other suppliers... and complete them with update method...
        }

        // TODO: delete deletes file.
    }

    /**
     * Download geonames' daily modifications file.
     *
     * @param DateTimeInterface $date
     * @return string
     */
    private function downloadModifications(DateTimeInterface $date): string
    {
        return $this->downloader->download($this->getModificationsUrlByDate($date), config('geonames.directory'));
    }

    /**
     * Download geonames' daily deletes file.
     *
     * @param DateTimeInterface $date
     * @return string
     */
    private function downloadDeletes(DateTimeInterface $date): string
    {
        return $this->downloader->download($this->getDeletesUrlByDate($date), config('geonames.directory'));
    }

    /**
     * Get the URL of the geonames' daily modifications file.
     *
     * @param DateTimeInterface $date
     * @return string
     */
    private function getModificationsUrlByDate(DateTimeInterface $date): string
    {
        return "http://download.geonames.org/export/dump/modifications-{$date->format('Y-m-d')}.txt";
    }

    /**
     * Get the URL of the geonames' daily deletes file.
     *
     * @param DateTimeInterface $date
     * @return string
     */
    private function getDeletesUrlByDate(DateTimeInterface $date): string
    {
        return "http://download.geonames.org/export/dump/deletes-{$date->format('Y-m-d')}.txt";
    }
}
