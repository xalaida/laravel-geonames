<?php

namespace Nevadskiy\Geonames\Parsers;

use Nevadskiy\Geonames\Files\FileReader;

class TimezonesParser
{
    public function each(): ?\Generator
    {
        $reader = FileReader::make(__DIR__.'/../../data/timeZones.txt');

        foreach ($reader->line() as $index => $rawLine) {
            $line = $this->parseLine($rawLine, $index);

            if (! $line) {
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
            0 => 'CountryCode',
            1 => 'TimeZoneId',
            2 => 'GMT offset 1. Jan 2020',
            3 => 'DST offset 1. Jul 2020',
            4 => 'rawOffset (independant of DST)',
        ];
    }

    /**
     * @param string $rawLine
     * @return array|null
     */
    private function parseLine(string $rawLine, int $index): ?array
    {
        if (! $rawLine) {
            return null;
        }

        $line = $this->mapLine($rawLine);

        if ($index === 0 && $this->isHeadRow($line)) {
            return null;
        }

        return $line;
    }

    /**
     * @param string $rawLine
     * @return array
     */
    private function mapLine(string $rawLine): array
    {
        return array_combine($this->fieldsMapping(), explode("\t", $rawLine));
    }

    /**
     * @param array $line
     * @return bool
     */
    private function isHeadRow(array $line): bool
    {
        return array_keys($line)[0] === array_values($line)[0];
    }
}
