<?php

namespace Nevadskiy\Geonames\Parsers;

use Generator;
use Nevadskiy\Geonames\Support\FileReader\FileReader;

interface Parser
{
    /**
     * Get all rows of the file by the given path.
     */
    public function all(string $path): array;

    /**
     * Parse a file line by line.
     */
    public function each(string $path): Generator;

    /**
     * Get the file reader instance.
     */
    public function getFileReader(): FileReader;

    /**
     * Set the parser fields.
     */
    public function setFields(array $fields): Parser;
}
