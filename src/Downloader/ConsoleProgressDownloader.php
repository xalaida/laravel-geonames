<?php

namespace Nevadskiy\Geonames\Downloader;

use Nevadskiy\Downloader\CurlDownloader;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Style\OutputStyle;
use Nevadskiy\Downloader\Downloader;

/**
 * @todo add possibility to specify custom progress format
 * @todo use human readable scale
 */
class ConsoleProgressDownloader implements Downloader
{
    /**
     * The cURL downloader instance.
     *
     * @var CurlDownloader
     */
    protected $downloader;

    /**
     * The symfony output instance.
     *
     * @var OutputStyle
     */
    protected $output;

    /**
     * The progress bar instance.
     *
     * @var ProgressBar
     */
    protected $progress;

    /**
     * Make a new downloader instance.
     */
    public function __construct(CurlDownloader $downloader, OutputStyle $output)
    {
        $this->downloader = $downloader;
        $this->output = $output;

        $this->setUpCurl();
    }

    /**
     * Set up the cURL handle instance.
     */
    protected function setUpCurl()
    {
        $this->downloader->withCurlOption(CURLOPT_NOPROGRESS, false);

        $this->downloader->withCurlOption(CURLOPT_PROGRESSFUNCTION, function ($ch, $downloadBytes, $downloadedBytes) {
            if ($downloadBytes) {
                $this->progress->setMaxSteps($downloadBytes);
            }

            if ($downloadedBytes) {
                $this->progress->setProgress($downloadedBytes);
            }
        });
    }

    /**
     * @inheritdoc
     */
    public function download(string $url, string $destination): string
    {
        $this->progress = $this->output->createProgressBar();

        $this->progress->start();

        $destination = $this->downloader->download($url, $destination);

        $this->progress->finish();

        return $destination;
    }
}
