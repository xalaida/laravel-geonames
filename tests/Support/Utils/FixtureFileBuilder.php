<?php

namespace Nevadskiy\Geonames\Tests\Support\Utils;

use Nevadskiy\Geonames\Geonames;

class FixtureFileBuilder
{
    /**
     * The geonames instance.
     *
     * @var Geonames
     */
    private $geonames;

    /**
     * Indicates if headers should be included.
     *
     * @var bool
     */
    protected $includeHeaders = false;

    /**
     * The row separator symbol.
     *
     * @var string
     */
    private $rowSeparator;

    /**
     * The column separator symbol.
     *
     * @var string
     */
    private $colSeparator;

    /**
     * Make a new fixture file builder instance.
     */
    public function __construct(Geonames $geonames, string $rowSeparator = "\n", string $colSeparator = "\t")
    {
        $this->geonames = $geonames;
        $this->rowSeparator = $rowSeparator;
        $this->colSeparator = $colSeparator;
    }

    /**
     * Include headers to the file.
     *
     * @return $this
     */
    public function withHeaders(): self
    {
        $this->includeHeaders = true;

        return $this;
    }

    /**
     * Build the file with the given data.
     */
    public function build(string $filename, array $data): string
    {
        $path = $this->getPath($filename);
        $this->prepareDirectory($path);
        $this->writeDataContent($path, $data);

        return $path;
    }

    /**
     * Get the fixture path.
     */
    protected function getPath(string $filename): string
    {
        return "{$this->geonames->directory()}/{$filename}";
    }

    /**
     * Prepare the directory.
     */
    protected function prepareDirectory(string $path): void
    {
        $dir = dirname($path);

        if (! is_dir($dir)) {
            mkdir($dir, 0, true);
        }
    }

    /**
     * Write the data to the file.
     */
    protected function writeDataContent(string $path, array $data): void
    {
        file_put_contents($path, $this->formatData($data));
    }

    /**
     * Format the data.
     */
    protected function formatData(array $data): string
    {
        if ($this->includeHeaders) {
            array_unshift($data, array_keys(reset($data)));
        }

        return implode($this->rowSeparator, array_map(function ($row) {
            return implode($this->colSeparator, $row);
        }, $data));
    }
}
