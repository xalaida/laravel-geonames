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
    protected $file;

    /**
     * The cache repository instance.
     *
     * @var Cache
     */
    protected $cache;

    /**
     * BaseFileReader constructor.
     */
    public function __construct(Cache $cache)
    {
        $this->cache = $cache;
    }

    /**
     * {@inheritdoc}
     */
    public function forEachLine(string $path, string $mode = 'rb'): Generator
    {
        $this->open($path, $mode);

        yield from $this->line();

        $this->close();
    }

    /**
     * {@inheritdoc}
     */
    public function getLinesCount(string $path): int
    {
        return $this->calculateLinesCount($path);

        // TODO: refactor caching without redis. consider using singleton decorator with in-memory cache.
//        return $this->cache->remember($this->getLinesCountCacheKey($path), now()->addHour(), function () use ($path) {
//        });
    }

    /**
     * Get the lines count cache key.
     */
    protected function getLinesCountCacheKey(string $path): string
    {
        $key = sprintf('%s:%s', $path, filesize($path));
        clearstatcache(true, $path);

        return $key;
    }

    /**
     * Calculate the lines count of a file by the given path.
     */
    protected function calculateLinesCount(string $path): int
    {
        $count = 0;

        foreach ($this->forEachLine($path) as $_) {
            $count++;
        }

        return $count;
    }

    /**
     * Open the file as resource.
     */
    public function open(string $path, string $mode = 'rb'): void
    {
        $this->file = fopen($path, $mode);
    }

    /**
     * Get the next line of the file resource.
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
