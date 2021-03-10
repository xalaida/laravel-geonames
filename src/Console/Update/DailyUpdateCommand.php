<?php

namespace Nevadskiy\Geonames\Console\Update;

use Illuminate\Console\Command;
use Illuminate\Contracts\Events\Dispatcher;
use Nevadskiy\Geonames\Events\GeonamesCommandReady;
use Nevadskiy\Geonames\Geonames;
use Nevadskiy\Geonames\Parsers\CountryInfoParser;
use Nevadskiy\Geonames\Parsers\DeletesParser;
use Nevadskiy\Geonames\Parsers\GeonamesParser;
use Nevadskiy\Geonames\Services\DownloadService;
use Nevadskiy\Geonames\Suppliers\CitySupplier;
use Nevadskiy\Geonames\Suppliers\ContinentSupplier;
use Nevadskiy\Geonames\Suppliers\CountrySupplier;
use Nevadskiy\Geonames\Suppliers\DivisionSupplier;
use Nevadskiy\Geonames\Support\Downloader\ConsoleDownloader;
use Nevadskiy\Geonames\Support\Downloader\Downloader;

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
     * The geonames instance.
     *
     * @var Geonames
     */
    protected $geonames;

    /**
     * The download service instance.
     *
     * @var DownloadService
     */
    protected $downloadService;

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
        Geonames $geonames,
        DownloadService $downloadService,
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
        $this->init($geonames, $downloadService, $dispatcher, $countryInfoParser, $geonamesParser, $deletesParser, $continentSupplier, $countrySupplier, $divisionSupplier, $citySupplier);
        $this->setUpDownloader($this->downloadService->getDownloader());

        $this->info('Start geonames daily updating.');
        $this->dispatcher->dispatch(new GeonamesCommandReady());

        $this->modify();
        $this->delete();

        $this->info('Daily update had been successfully done.');
    }

    /**
     * Init the command instance with all required services.
     */
    private function init(
        Geonames $geonames,
        DownloadService $downloadService,
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
        $this->geonames = $geonames;
        $this->downloadService = $downloadService;
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
     *
     * @param ConsoleDownloader|Downloader $downloader
     */
    private function setUpDownloader(ConsoleDownloader $downloader): void
    {
        $downloader->withProgressBar($this->getOutput());
    }

    /**
     * Modify changed items according to a geonames resource.
     */
    private function modify(): void
    {
        $this->info('Start processing modifications.');

        $modificationsPath = $this->downloadService->downloadDailyModifications();

        if ($this->geonames->shouldSupplyContinents()) {
            $this->continentSupplier->modifyMany($this->geonamesParser->forEach($modificationsPath));
        }

        if ($this->geonames->shouldSupplyCountries()) {
            $this->countrySupplier->setCountryInfos(
                $this->countryInfoParser->all($this->downloadService->downloadCountryInfoFile())
            );

            $this->countrySupplier->modifyMany($this->geonamesParser->forEach($modificationsPath));
        }

        if ($this->geonames->shouldSupplyDivisions()) {
            $this->divisionSupplier->modifyMany($this->geonamesParser->forEach($modificationsPath));
        }

        if ($this->geonames->shouldSupplyCities()) {
            $this->citySupplier->modifyMany($this->geonamesParser->forEach($modificationsPath));
        }

        // TODO: delete modifications file.
    }

    /**
     * Delete removed items according to a geonames resource.
     */
    private function delete(): void
    {
        $this->info('Start processing deletes.');

        $deletesPath = $this->downloadService->downloadDailyDeletes();

        if ($this->geonames->shouldSupplyContinents()) {
            $this->info('Start deleting continents');
            $this->continentSupplier->deleteMany($this->deletesParser->forEach($deletesPath));
        }

        if ($this->geonames->shouldSupplyCountries()) {
            $this->info('Start deleting countries');
            $this->countrySupplier->deleteMany($this->deletesParser->forEach($deletesPath));
        }

        if ($this->geonames->shouldSupplyDivisions()) {
            $this->info('Start deleting divisions');
            $this->divisionSupplier->deleteMany($this->deletesParser->forEach($deletesPath));
        }

        if ($this->geonames->shouldSupplyCities()) {
            $this->info('Start deleting cities');
            $this->citySupplier->deleteMany($this->deletesParser->forEach($deletesPath));
        }

        // TODO: delete deletes file.
    }
}
