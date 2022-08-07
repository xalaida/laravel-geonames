<?php

namespace Nevadskiy\Geonames\Reader;

class CountryInfoReader implements Reader
{
    /**
     * The reader instance.
     *
     * @var Reader
     */
    protected $reader;

    /**
     * The record headers.
     *
     * @var array
     */
    protected $headers = [
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

    /**
     * Make a new reader instance.
     */
    public function __construct(Reader $reader)
    {
        $this->reader = new HeadersReader($reader, $this->headers);
    }

    /**
     * @inheritdoc
     */
    public function getRecords(string $path): iterable
    {
        return $this->reader->getRecords($path);
    }
}
