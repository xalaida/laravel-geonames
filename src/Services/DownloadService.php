<?php

namespace Nevadskiy\Geonames\Services;

use Carbon\Carbon;
use InvalidArgumentException;
use Nevadskiy\Downloader\Downloader;

/**
 * TODO: add alternateNamesV2
 */
class DownloadService
{
    /**
     * The downloader instance.
     *
     * @var Downloader
     */
    protected $downloader;

    /**
     * The path of directory for downloads.
     *
     * @var string
     */
    protected $directory;

    /**
     * DownloadService constructor.
     */
    public function __construct(Downloader $downloader, string $directory)
    {
        $this->downloader = $downloader;
        // TODO: inject "directory" from service provider
        $this->directory = $directory ?: config('geonames.directory');
    }

    /**
     * Get the base URL for downloading geonames resources.
     */
    protected function getBaseUrl(): string
    {
        return 'https://download.geonames.org/export/dump/';
    }

    /**
     * Perform the download process.
     */
    public function download(string $url): string
    {
        return $this->downloader->download($url, $this->directory);
    }

    /**
     * Download a ZIP archive and return the main file from the archive.
     */
    public function downloadZip(string $url): string
    {
        return $this->download($url).DIRECTORY_SEPARATOR.pathinfo($url, PATHINFO_FILENAME);
    }

    /**
     * Download the geonames country info file.
     */
    public function downloadCountryInfo(): string
    {
        return $this->download("{$this->getBaseUrl()}countryInfo.txt");
    }

    /**
     * Download the all countries file.
     */
    public function downloadAllCountries(): string
    {
        return $this->downloadZip("{$this->getBaseUrl()}allCountries.zip");
    }

    /**
     * Download a single country file by the given country code.
     */
    public function downloadSingleCountry(string $country): string
    {
        return $this->downloadZip("{$this->getBaseUrl()}{$country}.zip");
    }

    /**
     * Download an alternate names file.
     */
    public function downloadAlternateNames(): string
    {
        return $this->downloadZip("{$this->getBaseUrl()}alternateNames.zip");
    }

    /**
     * Download an alternate names file of a single country by the given country code.
     */
    public function downloadSingleCountryAlternateNames(string $country): string
    {
        return $this->downloadZip("{$this->getBaseUrl()}alternatenames/{$country}.zip");
    }

    /**
     * Get the URL of the geonames daily deletes file.
     */
    protected function getDailyUpdateUrlByType(string $type): string
    {
        return "{$this->getBaseUrl()}{$type}-{$this->getGeonamesLastUpdateDate()->format('Y-m-d')}.txt";
    }

    /**
     * Get the previous date of geonames updates.
     */
    protected function getGeonamesLastUpdateDate(): Carbon
    {
        return Carbon::yesterday('UTC');
    }

    /**
     * Download geonames daily modifications file.
     */
    public function downloadDailyModifications(): string
    {
        return $this->download($this->getDailyUpdateUrlByType('modifications'));
    }

    /**
     * Download geonames daily deletes file.
     */
    public function downloadDailyDeletes(): string
    {
        return $this->download($this->getDailyUpdateUrlByType('deletes'));
    }

    /**
     * Download geonames daily alternate name modifications file.
     */
    public function downloadDailyAlternateNamesModifications(): string
    {
        return $this->download($this->getDailyUpdateUrlByType('alternateNamesModifications'));
    }

    /**
     * Download geonames daily alternate name deletes file.
     */
    public function downloadDailyAlternateNamesDeletes(): string
    {
        return $this->download($this->getDailyUpdateUrlByType('alternateNamesDeletes'));
    }

    /**
     * Download a "cities" file by the given population.
     */
    public function downloadCities(int $population): string
    {
        $this->ensurePopulationAvailable($population);

        return $this->downloadZip("{$this->getBaseUrl()}cities{$population}.zip");
    }

    /**
     * Download a file with no country related locations.
     */
    public function downloadNoCountry(): string
    {
        return $this->downloadZip("{$this->getBaseUrl()}no-country.zip");
    }

    /**
     * Assert that the given population is available to download.
     */
    protected function ensurePopulationAvailable(int $population): void
    {
        if (! in_array($population, $this->getPopulations())) {
            throw new InvalidArgumentException(
                vsprintf('There is no file with "%s" population. Specify one of: %s', [
                    $population,
                    implode(', ', $this->getPopulations()),
                ])
            );
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
}
