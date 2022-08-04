<?php

namespace Nevadskiy\Geonames\Downloader;

use Nevadskiy\Downloader\Downloader;

class HistoryDownloader implements Downloader
{
    /**
     * The base downloader instance.
     *
     * @var Downloader
     */
    protected $downloader;

    /**
     * The downloader history.
     *
     * @var array
     */
    protected $history = [];

    /**
     * Make a new downloader instance.
     */
    public function __construct(Downloader $downloader)
    {
        $this->downloader = $downloader;
    }

    /**
     * @inheritdoc
     */
    public function download(string $url, string $destination = null): string
    {
        if (! isset($this->history[$url])) {
            $this->history[$url] = $this->downloader->download($url, $destination);
        }

        return $this->history[$url];
    }
}
