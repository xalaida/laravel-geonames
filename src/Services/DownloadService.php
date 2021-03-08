<?php

namespace Nevadskiy\Geonames\Services;

use Illuminate\Support\Str;
use InvalidArgumentException;
use Nevadskiy\Geonames\Geonames;
use Nevadskiy\Geonames\Support\Downloader\ConsoleDownloader;
use Nevadskiy\Geonames\Support\Downloader\Downloader;

class DownloadService
{
    public const SOURCE_AUTO = 'auto';

    public const SOURCE_ALL_COUNTRIES = 'all_countries';

    public const SOURCE_SINGLE_COUNTRY = 'single_country';

    public const SOURCE_ONLY_CITIES = 'only_cities';

    /**
     * The downloader instance.
     *
     * @var Downloader
     */
    private $downloader;

    /**
     * A directory for geonames downloads.
     *
     * @var string
     */
    private $directory;

    /**
     * The geonames instance.
     *
     * @var Geonames
     */
    private $geonames;

    /**
     * DownloadService constructor.
     */
    public function __construct(Downloader $downloader, Geonames $geonames, string $directory)
    {
        $this->downloader = $downloader;
        $this->directory = $directory;
        $this->geonames = $geonames;
    }

    /**
     * Get the base URL for downloading geonames resources.
     *
     * @return string
     */
    public function getBaseUrl(): string
    {
        return 'http://download.geonames.org/export/dump/';
    }

    /**
     * Get the downloader instance.
     *
     * @return Downloader|ConsoleDownloader
     */
    public function getDownloader(): Downloader
    {
        return $this->downloader;
    }

    /**
     * Download geonames resource file.
     *
     * @return string
     */
    public function downloadGeonamesFile(): string
    {
        $path = $this->downloader->download($this->getGeonamesUrl(), $this->directory);

        if (is_array($path)) {
            $paths = array_filter($path, static function ($path) {
                return ! Str::contains($path, 'readme.txt');
            });

            return reset($paths);
        }

        return $path;
    }

    /**
     * Download geonames country info resource file.
     */
    public function downloadCountryInfoFile(): string
    {
        return $this->downloader->download($this->getCountryInfoUrl(), $this->directory);
    }

    /**
     * Get the URL of the geonames' main file.
     *
     * @return string
     */
    private function getGeonamesUrl(): string
    {
        if ($this->geonames->isAllCountriesSource()) {
            return $this->getAllCountriesUrl();
        }

        if ($this->geonames->isOnlyCitiesSource()) {
            return $this->getCitiesUrl($this->geonames->getPopulation());
        }

//        if ($this->geonames->isAutoSource()) {
//            // TODO: determine correct source.
//        }
    }

    /**
     * Get the country info resource URL.
     *
     * @return string
     */
    private function getCountryInfoUrl(): string
    {
        return $this->getBaseUrl() . 'countryInfo.txt';
    }

    /**
     * Get the all countries geonames resource URL.
     *
     * @return string
     */
    public function getAllCountriesUrl(): string
    {
        return $this->getBaseUrl() . 'allCountries.zip';
    }

    /**
     * Get the URL of the cities file with the given population.
     *
     * @return string
     */
    private function getCitiesUrl(int $population): string
    {
        $this->assertAvailablePopulation($population);

        return $this->getBaseUrl() . "cities{$population}.zip";
    }

    /**
     * Assert that the given population is available to download.
     *
     * @param int $population
     */
    private function assertAvailablePopulation(int $population): void
    {
        if (! in_array($population, $this->getPopulations())) {
            throw new InvalidArgumentException(
                vsprintf("There is no file with %s population. Specify one of %s", [
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
    public function getPopulations(): array
    {
        return [
            500,
            1000,
            5000,
            15000,
        ];
    }
}
