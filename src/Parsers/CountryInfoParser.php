<?php

namespace Nevadskiy\Geonames\Parsers;

use Nevadskiy\Geonames\Files\FileReader;

class CountryInfoParser
{
    public function each(): ?\Generator
    {
        $reader = FileReader::make(__DIR__.'/../../data/countryInfo.txt');

        foreach ($reader->line() as $rawLine) {
            if (! $rawLine || $rawLine[0] === '#') {
                continue;
            }

            yield $this->parseLine($rawLine);
        }
    }

    /**
     * Get the table fields mapping.
     *
     * @return array|string[]
     */
    protected function fieldsMapping(): array
    {
        return [
            0 => 'ISO',
            1 => 'ISO3',
            2 => 'ISO-Numeric',
            3 => 'fips',
            4 => 'Country',
            5 => 'Capital',
            6 => 'Area(in sq km)',
            7 => 'Population',
            8 => 'Continent',
            9 => 'tld',
            10 => 'CurrencyCode',
            11 => 'CurrencyName',
            12 => 'Phone',
            13 => 'Postal Code Format',
            14 => 'Postal Code Regex',
            15 => 'Languages',
            16 => 'geonameid',
            17 => 'neighbours',
            18 => 'EquivalentFipsCode',
        ];
    }

    /**
     * @param string $line
     * @return array
     */
    private function parseLine(string $line): array
    {
        return array_combine($this->fieldsMapping(), explode("\t", $line));
    }
}
