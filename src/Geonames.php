<?php

namespace Nevadskiy\Geonames;

use Nevadskiy\Geonames\Services\DownloadService;

class Geonames
{
    /**
     * The configuration array.
     *
     * @var array
     */
    private $config;

    /**
     * The geonames config wrapper class.
     */
    public function __construct(array $config)
    {
        $this->config = $config;
        $this->transformCountries();
    }

    /**
     * Determine whether the package should use default migrations.
     */
    public function shouldUseDefaultMigrations(): bool
    {
        return $this->config['default_migrations'];
    }

    /**
     * Get the geonames downloads directory.
     */
    public function directory(): string
    {
        return $this->config['directory'];
    }

    /**
     * Determine whether the all countries source is specified.
     */
    public function isAllCountriesSource(): bool
    {
        return $this->config['source'] === DownloadService::SOURCE_ALL_COUNTRIES;
    }

    /**
     * Determine whether the only cities source is specified.
     */
    public function isOnlyCitiesSource(): bool
    {
        return $this->config['source'] === DownloadService::SOURCE_ONLY_CITIES;
    }

    /**
     * Determine whether the single country source is specified.
     */
    public function isSingleCountrySource(): bool
    {
        return $this->config['source'] === DownloadService::SOURCE_SINGLE_COUNTRY;
    }

    /**
     * Determine whether the all countries is allowed to be supplied.
     */
    public function isAllCountriesAllowed(): bool
    {
        return (array) $this->config['filters']['countries'] === ['*'];
    }

    /**
     * Determine whether the package should supply continents to the database.
     */
    public function shouldSupplyContinents(): bool
    {
        if (! $this->isAllCountriesSource()) {
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
     */
    public function shouldSupplyCities(): bool
    {
        if (! $this->config['tables']['cities']) {
            return false;
        }

        return true;
    }

    /*
     * Get the items to be supplied.
     */
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

    /**
     * Get the population filter.
     */
    public function getPopulation(): int
    {
        return $this->config['filters']['population'];
    }

    /**
     * Determine whether the population is allowed.
     */
    public function isPopulationAllowed(int $population): bool
    {
        return $population >= $this->getPopulation();
    }

    /**
     * Get the countries filter.
     */
    public function getCountries(): array
    {
        return $this->config['filters']['countries'];
    }

    /**
     * Determine whether the country is allowed.
     */
    public function isCountryAllowed(string $code): bool
    {
        if ($this->getCountries() === ['*']) {
            return true;
        }

        return in_array($code, $this->getCountries(), true);
    }

    /**
     * Determine whether the given language code is allowed.
     */
    public function isLanguageAllowed(?string $code): bool
    {
        return (is_null($code) && $this->config['nullable_language'])
            || in_array($code, $this->config['languages'], true);
    }

    /**
     * Transform countries to uppercase.
     */
    protected function transformCountries(): void
    {
        $this->config['filters']['countries'] = array_map('strtoupper', (array) $this->config['filters']['countries']);
    }
}
