<?php

namespace Nevadskiy\Geonames;

use Nevadskiy\Geonames\Services\DownloadService;

class Geonames
{
    /**
     * @var array
     */
    private $config;

    /**
     * Geonames constructor.
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function shouldUseDefaultMigrations(): bool
    {
        return $this->config['default_migrations'];
    }

    public function isAutoSource(): bool
    {
        return $this->config['source'] === DownloadService::SOURCE_AUTO;
    }

    public function isAllCountriesSource(): bool
    {
        return $this->config['source'] === DownloadService::SOURCE_ALL_COUNTRIES;
    }

    public function isOnlyCitiesSource(): bool
    {
        return $this->config['source'] === DownloadService::SOURCE_AUTO;
    }

    public function isAllCountriesAllowed(): bool
    {
        return (array) $this->config['filters']['countries'] === ['*'];
    }

    /**
     * Determine whether the package should supply continents to the database.
     *
     * @return bool
     */
    public function shouldSupplyContinents(): bool
    {
        if (! $this->isAllCountriesSource() && ! $this->isAutoSource()) {
            return false;
        }

        if (! $this->isAllCountriesAllowed()) {
            return false;
        }

        if (! $this->config['tables']['continents']) {
            return false;
        }

        return true;
    }

    /**
     * Determine whether the package should supply countries to the database.
     *
     * @return bool
     */
    public function shouldSupplyCountries(): bool
    {
        if ($this->config['source'] === DownloadService::SOURCE_ONLY_CITIES) {
            return false;
        }

        if (! $this->config['tables']['countries']) {
            return false;
        }

        return true;
    }

    /**
     * Determine whether the package should supply divisions to the database.
     *
     * @return bool
     */
    public function shouldSupplyDivisions(): bool
    {
        if ($this->config['source'] === DownloadService::SOURCE_ONLY_CITIES) {
            return false;
        }

        if (! $this->config['tables']['divisions']) {
            return false;
        }

        return true;
    }

    /**
     * Determine whether the package should supply cities to the database.
     *
     * @return bool
     */
    public function shouldSupplyCities(): bool
    {
        if (! $this->config['tables']['cities']) {
            return false;
        }

        return true;
    }

    public function supply(): array
    {
        $suppliers = [];

        if ($this->shouldSupplyContinents()) {
            $suppliers[] = 'continents';
        }

        if ($this->shouldSupplyCountries()) {
            $suppliers[] = 'countries';
        }

        if ($this->shouldSupplyDivisions()) {
            $suppliers[] = 'divisions';
        }

        if ($this->shouldSupplyCities()) {
            $suppliers[] = 'cities';
        }

        return $suppliers;
    }
}
