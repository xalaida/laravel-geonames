<?php

namespace Nevadskiy\Geonames;

use Nevadskiy\Geonames\Reader\AlternateNamesDeletesReader;
use Nevadskiy\Geonames\Reader\AlternateNamesReader;
use Nevadskiy\Geonames\Reader\DeletesReader;
use Nevadskiy\Geonames\Reader\GeonamesReader;
use Nevadskiy\Geonames\Reader\Reader;
use Nevadskiy\Geonames\Services\DownloadService;

class GeonamesSource
{
    /**
     * The download service instance.
     *
     * @var DownloadService
     */
    protected $downloadService;

    /**
     * The reader instance.
     *
     * @var Reader
     */
    protected $reader;

    /**
     * Make a new geonames source instance.
     */
    public function __construct(DownloadService $downloadService, Reader $reader)
    {
        $this->downloadService = $downloadService;
        $this->reader = $reader;
    }

    /**
     * Get geonames records.
     */
    public function getRecords(array $countries = ['*']): iterable
    {
        $reader = new GeonamesReader($this->reader);

        if ($this->isWildcard($countries)) {
            return $reader->getRecords($this->downloadService->downloadAllCountries());
        }

        foreach ($countries as $country) {
            yield from $reader->getRecords($this->downloadService->downloadSingleCountry($country));
        }
    }

    /**
     * Get records of daily modifications.
     */
    public function getDailyModificationRecords(): iterable
    {
        return (new GeonamesReader($this->reader))->getRecords(
            $this->downloadService->downloadDailyModifications()
        );
    }

    /**
     * Get records of daily deletes.
     */
    public function getDailyDeleteRecords(): iterable
    {
        return (new DeletesReader($this->reader))->getRecords(
            $this->downloadService->downloadDailyDeletes()
        );
    }

    /**
     * Get alternate names records.
     */
    public function getAlternateNamesRecords(array $countries = ['*']): iterable
    {
        $reader = new AlternateNamesReader($this->reader);

        if ($this->isWildcard($countries)) {
            return $reader->getRecords($this->downloadService->downloadAlternateNames());
        }

        foreach ($countries as $country) {
            yield from $reader->getRecords($this->downloadService->downloadSingleCountryAlternateNames($country));
        }
    }

    /**
     * Get alternate names records of daily modification.
     */
    public function getAlternateNamesDailyModificationRecords(): iterable
    {
        return (new AlternateNamesReader($this->reader))->getRecords(
            $this->downloadService->downloadDailyAlternateNamesModifications()
        );
    }

    /**
     * Get alternate names records of daily deletes.
     */
    public function getAlternateNamesDailyDeleteRecords(): iterable
    {
        return (new AlternateNamesDeletesReader($this->reader))->getRecords(
            $this->downloadService->downloadDailyAlternateNamesDeletes()
        );
    }

    /**
     * Get only cities records.
     */
    public function getCitiesRecords(int $population): iterable
    {
        return (new GeonamesReader($this->reader))->getRecords(
            $this->downloadService->downloadCities($population)
        );
    }

    /**
     * Get no country records.
     */
    public function getNoCountryRecords(): iterable
    {
        return (new GeonamesReader($this->reader))->getRecords(
            $this->downloadService->downloadNoCountry()
        );
    }

    /**
     * Determine if the given countries is a wildcard.
     */
    protected function isWildcard(array $countries): bool
    {
        return count($countries) === 1 && $countries[0] === '*';
    }
}
