<?php

namespace Nevadskiy\Geonames\Services;

use Illuminate\Support\Arr;
use Nevadskiy\Geonames\Geonames;
use Nevadskiy\Geonames\Parsers\CountryInfoParser;
use Nevadskiy\Geonames\Parsers\DeletesParser;
use Nevadskiy\Geonames\Parsers\GeonamesParser;
use Nevadskiy\Geonames\Suppliers\CitySupplier;
use Nevadskiy\Geonames\Suppliers\ContinentSupplier;
use Nevadskiy\Geonames\Suppliers\CountrySupplier;
use Nevadskiy\Geonames\Suppliers\DivisionSupplier;
use Nevadskiy\Geonames\Suppliers\Supplier;

class SupplyService
{
    /**
     * The geonames instance.
     *
     * @var Geonames
     */
    private $geonames;

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
     * The deletes parser instance.
     *
     * @var DeletesParser
     */
    protected $deletesParser;

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
        GeonamesParser $geonamesParser,
        CountryInfoParser $countryInfoParser,
        DeletesParser $deletesParser,
        ContinentSupplier $continentSupplier,
        CountrySupplier $countrySupplier,
        DivisionSupplier $divisionSupplier,
        CitySupplier $citySupplier
    )
    {
        $this->geonames = $geonames;
        $this->geonamesParser = $geonamesParser;
        $this->countryInfoParser = $countryInfoParser;
        $this->deletesParser = $deletesParser;
        $this->continentSupplier = $continentSupplier;
        $this->countrySupplier = $countrySupplier;
        $this->divisionSupplier = $divisionSupplier;
        $this->citySupplier = $citySupplier;
    }

    /**
     * Add the country info by the given path.
     *
     * @param string $path
     */
    public function addCountryInfo(string $path): void
    {
        $this->countrySupplier->setCountryInfos($this->countryInfoParser->all($path));
    }

    /**
     * Insert dataset from the given path.
     *
     * @param string $path
     */
    public function insert(string $path): void
    {
        foreach ($this->suppliers() as $supplier) {
            $supplier->insertMany($this->geonamesParser->each($path));
        }
    }

    /**
     * Modify the database according to the given modifications path file.
     *
     * @param string $path
     */
    public function modify(string $path): void
    {
        foreach ($this->suppliers() as $supplier) {
             $supplier->modifyMany($this->geonamesParser->each($path));
        }
    }

    /**
     * Delete items from database according to the given deletes path file.
     *
     * @param string $path
     */
    public function delete(string $path): void
    {
        foreach ($this->suppliers() as $supplier) {
            $supplier->deleteMany($this->deletesParser->each($path));
        }
    }

    /**
     * Get suppliers.
     *
     * @return array<string, Supplier>
     */
    protected function suppliers(): array
    {
        return Arr::only($this->allSuppliers(), $this->geonames->supply());
    }

    /**
     * @return array
     */
    protected function allSuppliers(): array
    {
        return [
            'continents' => $this->continentSupplier,
            'countries' => $this->countrySupplier,
            'divisions' => $this->divisionSupplier,
            'cities' => $this->citySupplier,
        ];
    }
}
