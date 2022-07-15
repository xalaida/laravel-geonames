<?php

namespace Nevadskiy\Geonames\Reader;

class CountryInfoReader implements Reader
{
    /**
     * The reader instance.
     *
     * @var Reader
     */
    private $reader;

    /**
     * The record headers.
     *
     * @var array
     */
    private $headers = [
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
        $this->reader = $reader;
    }

    /**
     * @inheritdoc
     */
    public function getRecords(string $path): iterable
    {
        foreach ($this->reader->getRecords($path) as $record) {
            yield $this->map($record);
        }
    }

    /**
     * Map headers to the record.
     */
    protected function map($record): array
    {
        return array_combine($this->headers, $record);
    }
}
