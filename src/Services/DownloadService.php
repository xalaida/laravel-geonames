<?php

namespace Nevadskiy\Geonames\Services;

use Carbon\Carbon;
use InvalidArgumentException;
use Nevadskiy\Downloader\Downloader;

/**
 * TODO: rename
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
    public function download(string $path): string
    {
        $path = trim($path, '/');

        $destination = $this->downloader->download(
            $this->getBaseUrl().$path,
            trim($this->directory.DIRECTORY_SEPARATOR.trim(dirname($path), '.'), DIRECTORY_SEPARATOR)
        );

        if (substr($path, -4) === '.zip') {
            return $destination.DIRECTORY_SEPARATOR.pathinfo($path, PATHINFO_FILENAME).'.txt';
        }

        return $destination;
    }

    /**
     * Download the geonames country info file.
     */
    public function downloadCountryInfo(): string
    {
        return $this->download('countryInfo.txt');
    }

    /**
     * Download the all countries file.
     */
    public function downloadAllCountries(): string
    {
        return $this->download('allCountries.zip');
    }

    /**
     * Download a single country file by the given country code.
     */
    public function downloadSingleCountry(string $country): string
    {
        return $this->download("{$country}.zip");
    }

    /**
     * Download an alternate names file.
     */
    public function downloadAlternateNames(): string
    {
        return $this->download('alternateNames.zip');
    }

    /**
     * Download an alternate names version 2 file.
     */
    public function downloadAlternateNamesV2(): string
    {
        return $this->download('alternateNamesV2.zip');
    }

    /**
     * Download an alternate names file of a single country by the given country code.
     */
    public function downloadSingleCountryAlternateNames(string $country): string
    {
        return $this->download("alternatenames/{$country}.zip");
    }

    /**
     * Get the URL of the geonames daily deletes file.
     */
    protected function getDailyUpdateUrlByType(string $type): string
    {
        return "{$type}-{$this->getGeonamesLastUpdateDate()->format('Y-m-d')}.txt";
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

        return $this->download("cities{$population}.zip");
    }

    /**
     * Download a file with no country related locations.
     */
    public function downloadNoCountry(): string
    {
        return $this->download('no-country.zip');
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
