<?php

namespace Nevadskiy\Geonames\Suppliers;

interface CountrySupplier extends Supplier
{
    /**
     * Set the country infos list.
     *
     * @param array $countryInfo
     */
    public function setCountryInfos(array $countryInfo): void;
}
