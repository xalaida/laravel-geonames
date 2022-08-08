<?php

namespace Nevadskiy\Geonames\Reader;

use League\Csv\Reader as CsvReader;

class FileReader implements Reader
{
    /**
     * Indicates if commented lines should be skipped.
     */
    protected $skipComments = true;

    /**
     * @inheritdoc
     */
    public function getRecords(string $path): iterable
    {
        $reader = CsvReader::createFromPath($path);

        $reader->setDelimiter("\t");

        foreach ($reader->getRecords() as $record) {
            if ($this->filter($record)) {
                yield $record;
            }
        }
    }

    /**
     * Filter the record.
     */
    protected function filter(array $record): bool
    {
        if ($this->skipComments && $this->isCommented($record)) {
            return false;
        }

        return true;
    }

    /**
     * Determine if the given record is commented.
     */
    protected function isCommented(array $record): bool
    {
        return ($record[0][0] ?? null) === '#';
    }
}
