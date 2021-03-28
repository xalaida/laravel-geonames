<?php

namespace Nevadskiy\Geonames\Tests\Support\Utils\Fixtures;

class CountryInfoFixture extends Fixture
{
    /**
     * Get default attributes.
     */
    protected function defaults(): array
    {
        return [
            'ISO' => 'AE',
            'ISO3' => 'ARE',
            'ISO-Numeric' => '784',
            'fips' => 'AE',
            'Country' => 'United Arab Emirates',
            'Capital' => 'Abu Dhabi',
            'Area(in sq km)' => '82880',
            'Population' => '9630959',
            'Continent' => 'AS',
            'tld' => '.ae',
            'CurrencyCode' => 'AED',
            'CurrencyName' => 'Dirham',
            'Phone' => '',
            'Postal Code Format' => '',
            'Postal Code Regex' => '',
            'Languages' => 'ar-AE,fa,en,hi,ur',
            'geonameid' => '290557',
            'neighbours' => 'SA,OM',
            'EquivalentFipsCode' => '',
        ];
    }

    /**
     * Get the default filename.
     */
    protected function filename(): string
    {
        return 'country-info.txt';
    }
}
