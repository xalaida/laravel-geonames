<?php

namespace Nevadskiy\Geonames\Services;

use Illuminate\Support\Str;
use InvalidArgumentException;
use Nevadskiy\Geonames\Geonames;
use Nevadskiy\Geonames\Support\Downloader\ConsoleDownloader;
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
     * Download geonames source files.
     *
     * @return array
     */
    public function downloadSourceFiles(): array
    {
        $paths = [];

        foreach ($this->getSourceUrls() as $url) {
            $paths[] = $this->downloadSourceFile($url);
        }

        return $paths;
    }

    /**
     * Download geonames source file by the given URL.
     *
     * @return string
     */
    protected function downloadSourceFile(string $url): string
    {
        $path = $this->downloader->download($url, $this->directory);

        if (is_array($path)) {
            $paths = array_filter($path, static function ($path) {
                return ! Str::contains($path, 'readme.txt');
            });

            return reset($paths);
        }

        return $path;
    }

    /**
     * Download geonames country info source file.
     */
    public function downloadCountryInfoFile(): string
    {
        return $this->downloader->download($this->getCountryInfoUrl(), $this->directory);
    }

    /**
     * Get the URLs of the geonames sources.
     *
     * @return array
     */
    private function getSourceUrls(): array
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
    protected function getAllCountriesUrl(): string
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
     * Get the URL of the single country file by the given country codes.
     *
     * @return array
     */
    private function getSingleCountryUrls(array $countries): array
    {
        $this->assertCountryIsSpecified($countries);

        $urls = [];

        foreach ($countries as $country) {
            $urls[] = $this->getSingleCountryUrl($country);
        }

        return $urls;
    }

    /**
     * Get the URL of the single country file by the given country code.
     *
     * @return string
     */
    private function getSingleCountryUrl(string $code): string
    {
        return $this->getBaseUrl() . "{$code}.zip";
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
     * Assert that a country code is specified.
     */
    private function assertCountryIsSpecified(array $countries): void
    {
        if ($countries === ['*']) {
            throw new InvalidArgumentException('Specify a country code as in the geonames configuration file.');
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
