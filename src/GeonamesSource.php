<?php

namespace Nevadskiy\Geonames;

use Nevadskiy\Geonames\Reader\AlternateNamesDeletesReader;
use Nevadskiy\Geonames\Reader\AlternateNamesReader;
use Nevadskiy\Geonames\Reader\CountryInfoReader;
use Nevadskiy\Geonames\Reader\DeletesReader;
use Nevadskiy\Geonames\Reader\GeonamesReader;
use Nevadskiy\Geonames\Reader\Reader;

class GeonamesSource
{
    /**
     * The geonames downloader instance.
     *
     * @var GeonamesDownloader
     */
    protected $geonamesDownloader;

    /**
     * The reader instance.
     *
     * @var Reader
     */
    protected $reader;

    /**
     * Make a new geonames source instance.
     */
    public function __construct(GeonamesDownloader $geonamesDownloader, Reader $reader)
    {
        $this->geonamesDownloader = $geonamesDownloader;
        $this->reader = $reader;
    }

    /**
     * Get country info records.
     */
    public function getCountryInfoRecords(): iterable
    {
        return (new CountryInfoReader($this->reader))->getRecords(
            $this->geonamesDownloader->downloadCountryInfo()
        );
    }

    /**
     * Get geonames records.
     */
    public function getRecords(array $countries = ['*']): iterable
    {
        $reader = new GeonamesReader($this->reader);

        if (! $this->isWildcard($countries)) {
            foreach ($countries as $country) {
                yield from $reader->getRecords($this->geonamesDownloader->downloadSingleCountry($country));
            }
        } else {
            yield from $reader->getRecords($this->geonamesDownloader->downloadAllCountries());
        }
    }

    /**
     * Get records of daily modifications.
     */
    public function getDailyModificationRecords(): iterable
    {
        return (new GeonamesReader($this->reader))->getRecords(
            $this->geonamesDownloader->downloadDailyModifications()
        );
    }

    /**
     * Get records of daily deletes.
     */
    public function getDailyDeleteRecords(): iterable
    {
        return (new DeletesReader($this->reader))->getRecords(
            $this->geonamesDownloader->downloadDailyDeletes()
        );
    }

    /**
     * Get alternate names records.
     */
    public function getAlternateNamesRecords(array $countries = ['*']): iterable
    {
        $reader = new AlternateNamesReader($this->reader);

        if (! $this->isWildcard($countries)) {
            foreach ($countries as $country) {
                yield from $reader->getRecords($this->geonamesDownloader->downloadSingleCountryAlternateNames($country));
            }
        } else {
            yield from $reader->getRecords($this->geonamesDownloader->downloadAlternateNamesV2());
        }
    }

    /**
     * Get alternate names records of daily modification.
     */
    public function getAlternateNamesDailyModificationRecords(): iterable
    {
        return (new AlternateNamesReader($this->reader))->getRecords(
            $this->geonamesDownloader->downloadDailyAlternateNamesModifications()
        );
    }

    /**
     * Get alternate names records of daily deletes.
     */
    public function getAlternateNamesDailyDeleteRecords(): iterable
    {
        return (new AlternateNamesDeletesReader($this->reader))->getRecords(
            $this->geonamesDownloader->downloadDailyAlternateNamesDeletes()
        );
    }

    /**
     * Get only cities records.
     */
    public function getCitiesRecords(int $population): iterable
    {
        return (new GeonamesReader($this->reader))->getRecords(
            $this->geonamesDownloader->downloadCities($population)
        );
    }

    /**
     * Get no country records.
     */
    public function getNoCountryRecords(): iterable
    {
        return (new GeonamesReader($this->reader))->getRecords(
            $this->geonamesDownloader->downloadNoCountry()
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
