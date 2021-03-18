<?php

namespace Nevadskiy\Geonames\Support\Downloader;

use Nevadskiy\Geonames\Support\Unzipper\Unzipper;

class UnzipperDownloader implements Downloader
{
    /**
     * The decorated downloader instance.
     *
     * @var Downloader
     */
    private $downloader;

    /**
     * The unzipper instance.
     *
     * @var Unzipper
     */
    private $unzipper;

    /**
     * UnzipperDownloader constructor.
     */
    public function __construct(Downloader $downloader, Unzipper $unzipper)
    {
        $this->downloader = $downloader;
        $this->unzipper = $unzipper;
    }

    /**
     * Download a file by the given url and unzip it if possible.
     *
     * @return array|string
     */
    public function download(string $url, string $directory, string $name = null)
    {
        $path = $this->downloader->download($url, $directory, $name);

        if ($this->unzipper->canBeUnzipped($path)) {
            return $this->unzipper->unzip($path);
        }

        return $path;
    }

    /**
     * {@inheritdoc}
     */
    public function force(): Downloader
    {
        return $this->downloader->force();
    }

    /**
     * {@inheritdoc}
     */
    public function onReady(callable $callback): void
    {
        $this->downloader->onReady($callback);
    }

    /**
     * {@inheritdoc}
     */
    public function onStep(callable $callback): void
    {
        $this->downloader->onStep($callback);
    }

    /**
     * {@inheritdoc}
     */
    public function onFinish(callable $callback): void
    {
        $this->downloader->onFinish($callback);
    }
}
