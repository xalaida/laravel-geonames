<?php

namespace Nevadskiy\Geonames\Support\FileDownloader;

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
