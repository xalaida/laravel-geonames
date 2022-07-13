<?php

namespace Nevadskiy\Geonames\Reader;

use League\Csv\Reader as CsvReader;

class FileReader implements Reader
{
    /**
     * @inheritdoc
     */
    public function getRecords(string $path): iterable
    {
        $reader = CsvReader::createFromPath($path);

        $reader->setDelimiter("\t");

        foreach ($reader->getRecords() as $record) {
            yield $record;
        }
    }
}
