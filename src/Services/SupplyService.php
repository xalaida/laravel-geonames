<?php

namespace Nevadskiy\Geonames\Services;

use Illuminate\Support\Arr;
use Nevadskiy\Geonames\Geonames;
use Nevadskiy\Geonames\Parsers\CountryInfoParser;
use Nevadskiy\Geonames\Parsers\GeonamesDeletesParser;
use Nevadskiy\Geonames\Parsers\GeonamesParser;
use Nevadskiy\Geonames\Suppliers\CitySupplier;
use Nevadskiy\Geonames\Suppliers\ContinentSupplier;
use Nevadskiy\Geonames\Suppliers\CountrySupplier;
use Nevadskiy\Geonames\Suppliers\DivisionSupplier;
use Nevadskiy\Geonames\Suppliers\Supplier;
use Psr\Log\LoggerInterface;

class SupplyService
{
    /**
     * The geonames instance.
     *
     * @var Geonames
     */
    protected $geonames;

    /**
     * The logger instance.
     *
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * The geonames parser instance.
     *
     * @var GeonamesParser
     */
    protected $geonamesParser;

    /**
     * The geonames country info parser instance.
     *
     * @var CountryInfoParser
     */
    protected $countryInfoParser;

    /**
     * The geonames deletes parser instance.
     *
     * @var GeonamesDeletesParser
     */
    protected $geonamesDeletesParser;

    /**
     * The continent supplier instance.
     *
     * @var ContinentSupplier
     */
    protected $continentSupplier;

    /**
     * The country supplier instance.
     *
     * @var CountrySupplier
     */
    protected $countrySupplier;

    /**
     * The division supplier instance.
     *
     * @var DivisionSupplier
     */
    protected $divisionSupplier;

    /**
     * The city supplier instance.
     *
     * @var CitySupplier
     */
    protected $citySupplier;

    /**
     * Make a new supply service instance.
     */
    public function __construct(
        Geonames $geonames,
        LoggerInterface $logger,
        GeonamesParser $geonamesParser,
        CountryInfoParser $countryInfoParser,
        GeonamesDeletesParser $geonamesDeletesParser,
        ContinentSupplier $continentSupplier,
        CountrySupplier $countrySupplier,
        DivisionSupplier $divisionSupplier,
        CitySupplier $citySupplier
    ) {
        $this->geonames = $geonames;
        $this->logger = $logger;
        $this->geonamesParser = $geonamesParser;
        $this->countryInfoParser = $countryInfoParser;
        $this->geonamesDeletesParser = $geonamesDeletesParser;
        $this->continentSupplier = $continentSupplier;
        $this->countrySupplier = $countrySupplier;
        $this->divisionSupplier = $divisionSupplier;
        $this->citySupplier = $citySupplier;
    }

    /**
     * Add the country info by the given path.
     */
    public function addCountryInfo(string $path): void
    {
        $this->countrySupplier->setCountryInfos($this->countryInfoParser->all($path));
    }

    /**
     * Insert dataset from the given path.
     */
    public function insert(string $path): void
    {
        foreach ($this->suppliers() as $type => $supplier) {
            $this->logger->info("Inserting the {$type} geonames type.");
            $supplier->insertMany($this->geonamesParser->each($path));
        }
    }

    /**
     * Modify the database according to the given modifications path file.
     */
    public function modify(string $path): void
    {
        foreach ($this->suppliers() as $supplier) {
            $supplier->modifyMany($this->geonamesParser->each($path));
        }
    }

    /**
     * Delete items from database according to the given deletes path file.
     */
    public function delete(string $path): void
    {
        foreach ($this->suppliers() as $supplier) {
            $supplier->deleteMany($this->geonamesDeletesParser->each($path));
        }
    }

    /**
     * Get suppliers.
     *
     * @return array<string, Supplier>
     */
    protected function suppliers(): array
    {
        return Arr::only($this->allSuppliers(), array_keys($this->geonames->modelClasses()));
    }

    /**
     * Get all available suppliers.
     */
    protected function allSuppliers(): array
    {
        return [
            'continent' => $this->continentSupplier,
            'country' => $this->countrySupplier,
            'division' => $this->divisionSupplier,
            'city' => $this->citySupplier,
        ];
    }
}
