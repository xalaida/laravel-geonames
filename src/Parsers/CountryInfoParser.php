<?php

namespace Nevadskiy\Geonames\Parsers;

class CountryInfoParser extends Parser
{
    /**
     * @inheritDoc
     */
    protected function fieldsMapping(): array
    {
        return [
            'ISO',
            'ISO3',
            'ISO-Numeric',
            'fips',
            'Country',
            'Capital',
            'Area(in sq km)',
            'Population',
            'Continent',
            'tld',
            'CurrencyCode',
            'CurrencyName',
            'Phone',
            'Postal Code Format',
            'Postal Code Regex',
            'Languages',
            'geonameid',
            'neighbours',
            'EquivalentFipsCode',
        ];
    }
}
