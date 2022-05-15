<?php

namespace Nevadskiy\Geonames\Services;

use Carbon\Carbon;
use InvalidArgumentException;
use Nevadskiy\Geonames\Geonames;
use Nevadskiy\Geonames\Support\Downloader\Downloader;

class DownloadService
{
    /**
     * The all countries source.
     * It's a huge source that contains everything, unzipped size is about 1.5GB.
     *
     * Source URL: http://download.geonames.org/export/dump/allCountries.zip
     */
    public const SOURCE_ALL_COUNTRIES = 'all_countries';

    /**
     * The single country source.
     * Use it when you need dataset that belongs to one or multiple countries.
     * Continents table is not available with this source.
     * The country codes must be specified as countries filter configuration.
     *
     * Example of source URL for the US: http://download.geonames.org/export/dump/US.zip
     */
    public const SOURCE_SINGLE_COUNTRY = 'single_country';

    /**
     * The only cities source.
     * Use it when you only need the cities table.
     * Continents, countries and divisions is not available with this source.
     * Also, you need to specify population filter for the specific source file.
     *
     * Example of source URL for cities with population above 15000: http://download.geonames.org/export/dump/cities15000.zip
     */
    public const SOURCE_ONLY_CITIES = 'only_cities';

    /**
     * The downloader instance.
     *
     * @var Downloader
     */
    protected $downloader;

    /**
     * The geonames instance.
     *
     * @var Geonames
     */
    protected $geonames;

    /**
     * DownloadService constructor.
     */
    public function __construct(Downloader $downloader, Geonames $geonames)
    {
        $this->downloader = $downloader;
        $this->geonames = $geonames;
    }

    /**
     * Download geonames source files.
     */
    public function downloadSourceFiles(): array
    {
        $paths = [];

        foreach ($this->getSourceUrls() as $url) {
            $paths[] = $this->download($url);
        }

        return $paths;
    }

    /**
     * Download geonames alternate names resources.
     */
    public function downloaderAlternateNames(): array
    {
        $paths = [];

        foreach ($this->getAlternateNamesUrl() as $url) {
            $paths[] = $this->download($url);
        }

        return $paths;
    }

    /**
     * Download alternate names file.
     */
    public function downloadAlternateNames(): string
    {
        return $this->download($this->getAllAlternateNamesUrl());
    }

    /**
     * Download geonames daily modifications file.
     */
    public function downloadDailyModifications(): string
    {
        return $this->download($this->getDailyModificationsUrl());
    }

    /**
     * Download geonames daily deletes file.
     */
    public function downloadDailyDeletes(): string
    {
        return $this->download($this->getDailyDeletesUrl());
    }

    /**
     * Download geonames daily alternate name modifications file.
     */
    public function downloadDailyAlternateNamesModifications(): string
    {
        return $this->download($this->getDailyAlternateNamesModificationsUrl());
    }

    /**
     * Download geonames daily alternate name deletes file.
     */
    public function downloadDailyAlternateNamesDeletes(): string
    {
        return $this->download($this->getDailyAlternateNamesDeletesUrl());
    }

    /**
     * Download geonames country info file.
     */
    public function downloadCountryInfo(): string
    {
        return $this->download($this->getCountryInfoUrl());
    }

    /**
     * Download the all countries file.
     */
    public function downloadAllCountries(): string
    {
        return $this->download($this->getAllCountriesUrl());
    }

    /**
     * Perform the downloading process.
     *
     * @return array|string
     */
    protected function download(string $url)
    {
        return $this->downloader->download($url, $this->geonames->directory());
    }

    /**
     * Get the URLs of the geonames sources.
     */
    protected function getSourceUrls(): array
    {
        if ($this->geonames->isOnlyCitiesSource()) {
            return [$this->getCitiesUrl($this->geonames->getPopulation())];
        }

        if ($this->geonames->isSingleCountrySource()) {
            return $this->getSingleCountryUrls($this->geonames->getCountries());
        }

        return [$this->getAllCountriesUrl()];
    }

    /**
     * Get the URL of the cities file with the given population.
     */
    protected function getCitiesUrl(int $population): string
    {
        $this->assertAvailablePopulation($population);

        return "{$this->getBaseUrl()}cities{$population}.zip";
    }

    /**
     * Get the URL of the single country file by the given country codes.
     */
    protected function getSingleCountryUrls(array $countries): array
    {
        $this->assertCountryIsSpecified($countries);

        $urls = [];

        foreach ($countries as $country) {
            $urls[] = $this->getSingleCountryUrl($country);
        }

        return $urls;
    }

    /**
     * Get the URLs of the single country of the alternate names files by the given country codes.
     */
    protected function getSingleCountryAlternateNamesUrls(array $countries): array
    {
        $this->assertCountryIsSpecified($countries);

        $urls = [];

        foreach ($countries as $country) {
            $urls[] = $this->getSingleCountryAlternateNamesUrl($country);
        }

        return $urls;
    }

    /**
     * Get the country info resource URL.
     */
    protected function getCountryInfoUrl(): string
    {
        return "{$this->getBaseUrl()}countryInfo.txt";
    }

    /**
     * Get the geonames alternate names resource URLs.
     */
    protected function getAlternateNamesUrl(): array
    {
        if ($this->geonames->isSingleCountrySource()) {
            return $this->getSingleCountryAlternateNamesUrls($this->geonames->getCountries());
        }

        return [$this->getAllAlternateNamesUrl()];
    }

    /**
     * Get the all countries geonames resource URL.
     */
    protected function getAllCountriesUrl(): string
    {
        return "{$this->getBaseUrl()}allCountries.zip";
    }

    /**
     * Get the URL of the single country file by the given country code.
     */
    protected function getSingleCountryUrl(string $code): string
    {
        return "{$this->getBaseUrl()}{$code}.zip";
    }

    /**
     * Get the URL of the single country of the alternate name file by the given country code.
     */
    protected function getSingleCountryAlternateNamesUrl(string $code): string
    {
        return "{$this->getBaseUrl()}alternatenames/{$code}.zip";
    }

    /**
     * Get the previous date of geonames updates.
     */
    protected function getGeonamesLastUpdateDate(): Carbon
    {
        return Carbon::yesterday('UTC');
    }

    /**
     * Get the URL of the geonames daily modifications file.
     */
    protected function getDailyModificationsUrl(): string
    {
        return $this->getDailyUpdateUrlByType('modifications');
    }

    /**
     * Get the URL of the geonames daily deletes file.
     */
    protected function getDailyDeletesUrl(): string
    {
        return $this->getDailyUpdateUrlByType('deletes');
    }

    /**
     * Get the URL of the geonames daily alternate names modifications file.
     */
    protected function getDailyAlternateNamesModificationsUrl(): string
    {
        return $this->getDailyUpdateUrlByType('alternateNamesModifications');
    }

    /**
     * Get the URL of the geonames daily alternate names deletes file.
     */
    protected function getDailyAlternateNamesDeletesUrl(): string
    {
        return $this->getDailyUpdateUrlByType('alternateNamesDeletes');
    }

    /**
     * Get the URL of the geonames daily deletes file.
     */
    protected function getDailyUpdateUrlByType(string $type): string
    {
        return "{$this->getBaseUrl()}{$type}-{$this->getGeonamesLastUpdateDate()->format('Y-m-d')}.txt";
    }

    /**
     * Get the base URL for downloading geonames resources.
     */
    protected function getBaseUrl(): string
    {
        return 'https://download.geonames.org/export/dump/';
    }

    /**
     * Assert that the given population is available to download.
     */
    protected function assertAvailablePopulation(int $population): void
    {
        if (! in_array($population, $this->getPopulations())) {
            throw new InvalidArgumentException(
                vsprintf('There is no file with %s population. Specify one of %s', [
                    $population,
                    implode(', ', $this->getPopulations()),
                ])
            );
        }
    }

    /**
     * Assert that a country code is specified.
     */
    protected function assertCountryIsSpecified(array $countries): void
    {
        if ($countries === ['*']) {
            throw new InvalidArgumentException('Specify a country code in the geonames configuration file.');
        }
    }

    /**
     * Get available populations for cities resource.
     *
     * @return int[]
     */
    protected function getPopulations(): array
    {
        return [
            500,
            1000,
            5000,
            15000,
        ];
    }

    /**
     * Get full alternate names URL.
     */
    protected function getAllAlternateNamesUrl(): string
    {
        return "{$this->getBaseUrl()}alternateNames.zip";
    }
}
