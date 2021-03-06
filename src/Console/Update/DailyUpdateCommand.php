<?php

namespace Nevadskiy\Geonames\Console\Update;

use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Console\Command;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\Facades\DB;
use Nevadskiy\Geonames\Events\GeonamesCommandReady;
use Nevadskiy\Geonames\Parsers\CountryInfoParser;
use Nevadskiy\Geonames\Parsers\DeletesParser;
use Nevadskiy\Geonames\Parsers\GeonamesParser;
use Nevadskiy\Geonames\Suppliers\CitySupplier;
use Nevadskiy\Geonames\Suppliers\ContinentSupplier;
use Nevadskiy\Geonames\Suppliers\CountrySupplier;
use Nevadskiy\Geonames\Suppliers\DivisionSupplier;
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
     * The geonames country info parser instance.
     *
     * @var CountryInfoParser
     */
    protected $countryInfoParser;

    /**
     * The geonames parser instance.
     *
     * @var GeonamesParser
     */
    protected $geonamesParser;

    /**
     * The deletes parser instance.
     *
     * @var DeletesParser
     */
    protected $deletesParser;

    /**
     * The continent supplier instance.
     *
     * @var ContinentSupplier
     */
    protected $continentSupplier;

    /**
     * The country supplier instance.
     *
     * @var CountrySupplier
     */
    protected $countrySupplier;

    /**
     * The division supplier instance.
     *
     * @var DivisionSupplier
     */
    protected $divisionSupplier;

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
        CountryInfoParser $countryInfoParser,
        GeonamesParser $geonamesParser,
        DeletesParser $deletesParser,
        ContinentSupplier $continentSupplier,
        CountrySupplier $countrySupplier,
        DivisionSupplier $divisionSupplier,
        CitySupplier $citySupplier
    ): void
    {
        $this->init($downloader, $dispatcher, $countryInfoParser, $geonamesParser, $deletesParser, $continentSupplier, $countrySupplier, $divisionSupplier, $citySupplier);

        // TODO: refactor with service container configure
        $this->setUpDownloader($downloader);

        $this->dispatcher->dispatch(new GeonamesCommandReady());

        $this->info('Start geonames daily updating.');

        $date = $this->getPreviousDate();

        // TODO: remove the line when code will be ready
        DB::beginTransaction();

        $this->modify($date);
        $this->delete($date);

        // TODO: need to process the file 4 times to avoid FOREIGN KEY CONSTRAINT error in the case when new city is added, but division in not exists yet.

        // TODO: remove the line when code will be ready
        DB::rollBack();

        $this->info('Daily update had been successfully done.');
    }

    /**
     * Init the command instance with all required services.
     */
    private function init(
        ConsoleFileDownloader $downloader,
        Dispatcher $dispatcher,
        CountryInfoParser $countryInfoParser,
        GeonamesParser $geonamesParser,
        DeletesParser $deletesParser,
        ContinentSupplier $continentSupplier,
        CountrySupplier $countrySupplier,
        DivisionSupplier $divisionSupplier,
        CitySupplier $citySupplier
    ): void
    {
        $this->downloader = $downloader;
        $this->dispatcher = $dispatcher;
        $this->countryInfoParser = $countryInfoParser;
        $this->geonamesParser = $geonamesParser;
        $this->deletesParser = $deletesParser;
        $this->continentSupplier = $continentSupplier;
        $this->countrySupplier = $countrySupplier;
        $this->divisionSupplier = $divisionSupplier;
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

        // TODO: download countryInfo and update using it.

        $this->continentSupplier->init();
        foreach ($this->geonamesParser->forEach($modificationsPath) as $id => $data) {
            if ($this->continentSupplier->modify($id, $data)) {
                $this->info('Continent has been modified: '. $id);
            }
        }

        $this->countrySupplier->init();
        foreach ($this->geonamesParser->forEach($modificationsPath) as $id => $data) {
            if ($this->countrySupplier->modify($id, $data)) {
                $this->info('Country has been modified: '. $id);
            }
        }

        $this->divisionSupplier->init();
        foreach ($this->geonamesParser->forEach($modificationsPath) as $id => $data) {
            if ($this->divisionSupplier->modify($id, $data)) {
                $this->info('Division has been modified: '. $id);
            }
        }

        $this->citySupplier->init();
        foreach ($this->geonamesParser->forEach($modificationsPath) as $id => $data) {
            if ($this->citySupplier->modify($id, $data)) {
                $this->info('City has been modified: '. $id);
            }
        }

        // TODO: delete modifications file.
    }

    /**
     * Delete removed items according to a geonames' resource.
     */
    private function delete(DateTimeInterface $previousDate): void
    {
        $deletesPath = $this->downloadDeletes($previousDate);

        foreach ($this->deletesParser->forEach($deletesPath) as $id => $data) {
            if ($this->continentSupplier->delete($id, $data)) {
                $this->info('Continent has been deleted: '. $id);
            }
        }

        foreach ($this->deletesParser->forEach($deletesPath) as $id => $data) {
            if ($this->countrySupplier->delete($id, $data)) {
                $this->info('Country has been deleted: '. $id);
            }
        }

        foreach ($this->deletesParser->forEach($deletesPath) as $id => $data) {
            if ($this->divisionSupplier->delete($id, $data)) {
                $this->info('Division has been deleted: '. $id);
            }
        }

        foreach ($this->deletesParser->forEach($deletesPath) as $id => $data) {
            if ($this->citySupplier->delete($id, $data)) {
                $this->info('City has been deleted: '. $id);
            }
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
