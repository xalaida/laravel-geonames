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
     * @param string $url
     * @param string $directory
     * @param string|null $name
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
     * @inheritDoc
     */
    public function force(): Downloader
    {
        return $this->downloader->force();
    }

    /**
     * @inheritDoc
     */
    public function update(): Downloader
    {
        return $this->downloader->update();
    }

    /**
     * @inheritDoc
     */
    public function onReady(callable $callback): void
    {
        $this->downloader->onReady($callback);
    }

    /**
     * @inheritDoc
     */
    public function onStep(callable $callback): void
    {
        $this->downloader->onStep($callback);
    }

    /**
     * @inheritDoc
     */
    public function onFinish(callable $callback): void
    {
        $this->downloader->onFinish($callback);
    }
}
