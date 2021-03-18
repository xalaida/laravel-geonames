<?php

namespace Nevadskiy\Geonames\Support\FileReader;

use Generator;

interface FileReader
{
    /**
     * Read the given file line by line.
     */
    public function forEachLine(string $path, string $mode = 'rb'): Generator;

    /**
     * Get the lines count of the file by the given path.
     */
    public function getLinesCount(string $path): int;
}
