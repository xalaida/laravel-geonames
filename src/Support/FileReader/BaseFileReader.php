<?php

namespace Nevadskiy\Geonames\Support\FileReader;

use Generator;
use Illuminate\Contracts\Cache\Repository as Cache;

class BaseFileReader implements FileReader
{
    /**
     * The file resource to be read.
     *
     * @var resource
     */
    private $file;

    /**
     * The cache repository instance.
     *
     * @var Cache
     */
    private $cache;

    /**
     * BaseFileReader constructor.
     */
    public function __construct(Cache $cache)
    {
        $this->cache = $cache;
    }

    /**
     * @inheritDoc
     */
    public function forEachLine(string $path, string $mode = 'rb'): Generator
    {
        $this->open($path, $mode);

        yield from $this->line();

        $this->close();
    }

    /**
     * @inheritDoc
     */
    public function getLinesCount(string $path): int
    {
        return $this->cache->remember($this->getLinesCountCacheKey($path), now()->addHour(), function () use ($path) {
            return $this->calculateLinesCount($path);
        });
    }

    /**
     * Get the lines count cache key.
     *
     * @param string $path
     * @return string
     */
    private function getLinesCountCacheKey(string $path): string
    {
        $key = sprintf("%s:%s", $path, filesize($path));
        clearstatcache(true, $path);

        return $key;
    }

    /**
     * Calculate the lines count of a file by the given path.
     *
     * @param string $path
     * @return int
     */
    private function calculateLinesCount(string $path): int
    {
        $count = 0;

        foreach ($this->forEachLine($path) as $_) {
            $count++;
        }

        return $count;
    }

    /**
     * Open the file as resource.
     *
     * @param string $path
     * @param string $mode
     */
    public function open(string $path, string $mode = 'rb'): void
    {
        $this->file = fopen($path, $mode);
    }

    /**
     * Get the next line of the file resource.
     *
     * @return Generator
     */
    public function line(): Generator
    {
        $line = 0;

        while (! feof($this->file)) {
            yield $line => rtrim(fgets($this->file), "\r\n");
            $line++;
        }
    }

    /**
     * Close the file resource.
     */
    public function close(): void
    {
        fclose($this->file);
    }
}
