<?php

namespace Nevadskiy\Geonames\Parsers;

use Nevadskiy\Geonames\Files\FileReader;

class GeonamesParser
{
    public function each(): ?\Generator
    {
        $reader = FileReader::make(__DIR__.'/../../data/UA.txt');

        foreach ($reader->line() as $i => $rawLine) {
            if (! $rawLine) {
                continue;
            }

            $line = $this->parseLine($rawLine);

            if ($i === 0 && $this->isHeadRow($line)) {
                continue;
            }

            yield $line;
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
            0 => 'geonameid', // integer id of record in geonames database
            1 => 'name', // name of geographical point (utf8) varchar(200)
            2 => 'asciiname', // name of geographical point in plain ascii characters, varchar(200)
            3 => 'alternatenames', // alternatenames, comma separated, ascii names automatically transliterated, convenience attribute from alternatename table, varchar(10000)
            4 => 'latitude', // latitude in decimal degrees (wgs84)
            5 => 'longitude', // longitude in decimal degrees (wgs84)
            6 => 'feature class', // see http://www.geonames.org/export/codes.html, char(1)
            7 => 'feature code', // see http://www.geonames.org/export/codes.html, varchar(10)
            8 => 'country code', // ISO-3166 2-letter country code, 2 characters
            9 => 'cc2', // alternate country codes, comma separated, ISO-3166 2-letter country code, 200 characters
            10 => 'admin1 code', // fipscode (subject to change to iso code), see exceptions below, see file admin1Codes.txt for display names of this code; varchar(20)
            11 => 'admin2 code', // code for the second administrative division, a county in the US, see file admin2Codes.txt; varchar(80)
            12 => 'admin3 code', // code for third level administrative division, varchar(20)
            13 => 'admin4 code', // code for fourth level administrative division, varchar(20)
            14 => 'population', // bigint (8 byte int)
            15 => 'elevation', // in meters, integer
            16 => 'dem', // digital elevation model, srtm3 or gtopo30, average elevation of 3''x3'' (ca 90mx90m) or 30''x30'' (ca 900mx900m) area in meters, integer. srtm processed by cgiar/ciat.
            17 => 'timezone', // the iana timezone id (see file timeZone.txt) varchar(40)
            18 => 'modification date', // date of last modification in yyyy-MM-dd format
        ];
    }

    /**
     * @param array $line
     * @return bool
     */
    private function isHeadRow(array $line): bool
    {
        return array_keys($line)[0] === array_values($line)[0];
    }

    /**
     * @param string $line
     * @return array
     */
    private function parseLine(string $line): array
    {
        $mappedLine = $this->mapLine($line);

        foreach ($mappedLine as $key => $value) {
            $mappedLine[$key] = $this->transformValue($value);
        }

        return $mappedLine;
    }

    /**
     * @param mixed $value
     * @return mixed
     */
    protected function transformValue($value)
    {
        return is_string($value) && $value === '' ? null : $value;
    }

    /**
     * @param string $line
     * @return array
     */
    private function mapLine(string $line): array
    {
        return array_combine($this->fieldsMapping(), explode("\t", $line));
    }
}
