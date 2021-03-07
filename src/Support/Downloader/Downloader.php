<?php

namespace Nevadskiy\Geonames\Support\Downloader;

interface Downloader
{
    /**
     * Download a file by the given url and returns the final path.
     *
     * @param string $url
     * @param string $directory
     * @param string|null $name
     * @return string|array
     */
    public function download(string $url, string $directory, string $name = null);

    /**
     * Enable overwriting files if a file already exists.
     */
    public function force(): Downloader;

    /**
     * Enable updating files if a file already exists with different size.
     */
    public function update(): Downloader;

    /**
     * Add the given callback to ready event.
     *
     * @param callable $callback
     */
    public function onReady(callable $callback): void;

    /**
     * Add the given callback to step event.
     *
     * @param callable $callback
     */
    public function onStep(callable $callback): void;

    /**
     * Add the given callback to finish event.
     *
     * @param callable $callback
     */
    public function onFinish(callable $callback): void;
}
