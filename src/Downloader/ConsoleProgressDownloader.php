<?php

namespace Nevadskiy\Geonames\Downloader;

use Illuminate\Console\OutputStyle;
use Nevadskiy\Downloader\CurlDownloader;
use Symfony\Component\Console\Helper\Helper;
use Symfony\Component\Console\Helper\ProgressBar;
use Nevadskiy\Downloader\Downloader;

class ConsoleProgressDownloader implements Downloader
{
    /**
     * The default downloader format name.
     */
    protected const FORMAT_DOWNLOADER = 'downloader';

    /**
     * The downloader format name when maximum steps are not available.
     */
    protected const FORMAT_DOWNLOADER_NOMAX = 'downloader_nomax';

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
     * A format of the progress bar.
     *
     * @var string|null
     */
    protected $format = self::FORMAT_DOWNLOADER;

    /**
     * Indicates if a new line should be printed when progress bar finishes.
     *
     * @var string
     */
    protected $printsNewLine = true;

    /**
     * The progress bar instance.
     *
     * @var ProgressBar
     */
    private $progress;

    /**
     * Make a new downloader instance.
     */
    public function __construct(CurlDownloader $downloader, OutputStyle $output)
    {
        $this->downloader = $downloader;
        $this->output = $output;

        $this->setUpCurlDownloader();
        $this->setFormatDefinition();
    }

    /**
     * Specify a format of the progress bar.
     */
    public function setFormat(string $format): void
    {
        $this->format = $format;
    }

    /**
     * Set up the cURL downloader instance.
     */
    protected function setUpCurlDownloader(): void
    {
        $this->downloader->onProgress(function (int $total, int $loaded) {
            if ($total) {
                $this->progress->setMaxSteps($total);
            }

            if ($loaded) {
                $this->progress->setProgress($loaded);
            }
        });
    }

    /**
     * Set up a human-readable progress format.
     */
    protected function setFormatDefinition(): void
    {
        ProgressBar::setFormatDefinition(
            self::FORMAT_DOWNLOADER,
            ' %size_loaded%/%size_total% [%bar%] %percent:3s%% %elapsed:6s%/%estimated:-6s%'
        );

        ProgressBar::setFormatDefinition(
            self::FORMAT_DOWNLOADER_NOMAX,
            ' %size_loaded% [%bar%] %percent:3s%% %elapsed:6s%'
        );

        ProgressBar::setPlaceholderFormatterDefinition('size_loaded', function (ProgressBar $bar) {
            return Helper::formatMemory($bar->getProgress());
        });

        ProgressBar::setPlaceholderFormatterDefinition('size_total', function (ProgressBar $bar) {
            return Helper::formatMemory($bar->getMaxSteps());
        });
    }

    /**
     * @inheritdoc
     */
    public function download(string $url, string $destination = null): string
    {
        $this->progress = $this->output->createProgressBar();

        if ($this->format) {
            $this->progress->setFormat($this->format);
        }

        $this->progress->setMessage($url, 'url');

        $this->progress->start();

        $destination = $this->downloader->download($url, $destination);

        $this->progress->finish();

        if ($this->printsNewLine) {
            $this->output->newLine();
        }

        return $destination;
    }
}
