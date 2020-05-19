<?php

namespace Nevadskiy\Geonames\Files;

use Generator;

class FileReader
{
    /**
     * The open file resource.
     *
     * @var resource
     */
    protected $file;

    /**
     * FileReader constructor.
     *
     * @param string $path
     * @param string $mode
     */
    public function __construct(string $path, string $mode)
    {
        $this->open($path, $mode);
    }

    /**
     * FileReader static constructor.
     *
     * @param string $path
     * @param string $mode
     * @return FileReader
     */
    public static function make(string $path, string $mode = 'rb'): FileReader
    {
        return new static($path, $mode);
    }

    /**
     * Open the file as resource.
     *
     * @param string $path
     * @param string $mode
     */
    protected function open(string $path, string $mode): void
    {
        $this->file = fopen($path, $mode);
    }

    /**
     * Get the next line of the file resource.
     *
     * @return Generator|null
     */
    public function line(): ?Generator
    {
        while (! feof($this->file)) {
            yield rtrim(fgets($this->file), "\r\n");
        }
    }

    /**
     * Close the file resource.
     */
    protected function close(): void
    {
        fclose($this->file);
    }

    /**
     * FileReader destructor.
     */
    public function __destruct()
    {
        $this->close();
    }
}
