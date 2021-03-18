<?php

namespace Nevadskiy\Geonames\Suppliers;

interface CountrySupplier extends Supplier
{
    /**
     * Set the country infos list.
     */
    public function setCountryInfos(array $countryInfo): void;
}
