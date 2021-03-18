<?php

namespace Nevadskiy\Geonames\Support\Downloader;

interface Downloader
{
    /**
     * Download a file by the given url and returns the final path.
     *
     * @return string|array
     */
    public function download(string $url, string $directory, string $name = null);

    /**
     * Enable overwriting files if a file already exists.
     */
    public function force(): self;

    /**
     * Add the given callback to ready event.
     */
    public function onReady(callable $callback): void;

    /**
     * Add the given callback to step event.
     */
    public function onStep(callable $callback): void;

    /**
     * Add the given callback to finish event.
     */
    public function onFinish(callable $callback): void;
}
