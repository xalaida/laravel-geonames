<?php

namespace Nevadskiy\Geonames;

use Carbon\Carbon;
use InvalidArgumentException;
use Nevadskiy\Downloader\Downloader;
use ZipArchive;
use function dirname;
use function in_array;
use const DIRECTORY_SEPARATOR;

class GeonamesDownloader
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
     * Make a new downloader instance.
     */
    public function __construct(Downloader $downloader, string $directory)
    {
        $this->downloader = $downloader;
        $this->directory = rtrim($directory, DIRECTORY_SEPARATOR);
    }

    /**
     * Perform the download process.
     */
    public function download(string $path): string
    {
        $destination = $this->downloader->download(
            $this->url($path),
            $this->directory . DIRECTORY_SEPARATOR . trim(dirname($path), '.'),
        );

        if (pathinfo($destination, PATHINFO_EXTENSION) === 'zip') {
            return $this->extract($destination);
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
     * Get the base URL for downloading geonames resources.
     */
    protected function baseUrl(): string
    {
        return 'https://download.geonames.org/export/dump/';
    }

    /**
     * Get the final URL to the given geonames resource path.
     */
    protected function url(string $path): string
    {
        return $this->baseUrl() . ltrim($path, '/');
    }

    /**
     * Extract a file from an archive by the given path.
     *
     * @TODO consider extracting into separate `Extractor` class
     */
    protected function extract(string $path): string
    {
        $directory = pathinfo($path, PATHINFO_DIRNAME);

        $file = pathinfo($path, PATHINFO_FILENAME) . '.txt';

        $destination = $directory . DIRECTORY_SEPARATOR . $file;

        if (! file_exists($destination)) {
            $zip = new ZipArchive();
            $zip->open($path);
            $zip->extractTo($directory, $file);
            $zip->close();
        }

        return $destination;
    }

    /**
     * Assert that the given population is available to download.
     */
    protected function ensurePopulationAvailable(int $population): void
    {
        if (! in_array($population, $this->getPopulations(), true)) {
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
