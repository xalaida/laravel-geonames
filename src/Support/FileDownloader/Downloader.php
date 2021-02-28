<?php

namespace Nevadskiy\Geonames\Support\FileDownloader;

interface Downloader
{
    /**
     * Download a file by the given url.
     *
     * @param string $url
     * @param string $directory
     * @param string|null $name
     * @return string realpath
     */
    public function download(string $url, string $directory, string $name = null): string;
}
