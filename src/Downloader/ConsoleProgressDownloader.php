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
     * The default progress format name of the downloader.
     */
    protected const PROGRESS_FORMAT = 'downloader';

    /**
     * The progress format name of the downloader when maximum steps are not available.
     */
    protected const PROGRESS_FORMAT_NOMAX = 'downloader_nomax';

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
    protected $format = self::PROGRESS_FORMAT;

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
    protected $progress;

    /**
     * Indicates if the current downloading process is finished.
     *
     * @var bool
     */
    private $finished = false;

    /**
     * Make a new downloader instance.
     */
    public function __construct(CurlDownloader $downloader, OutputStyle $output)
    {
        $this->downloader = $downloader;
        $this->output = $output;

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
     * Set up a human-readable progress format.
     */
    protected function setFormatDefinition(): void
    {
        ProgressBar::setFormatDefinition(
            self::PROGRESS_FORMAT,
            ' %size_loaded%/%size_total% [%bar%] %percent:3s%% %elapsed:6s%/%estimated:-6s%'
        );

        ProgressBar::setFormatDefinition(
            self::PROGRESS_FORMAT_NOMAX,
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
        $this->setUpProgress($url);

        return $this->downloader->download($url, $destination);
    }

    /**
     * Set up progress hook.
     */
    protected function setUpProgress(string $url): void
    {
        $this->finished = false;

        $this->downloader->onProgress(function (int $total, int $loaded) use ($url) {
            if ($this->finished) {
                return;
            }

            if (! $loaded) {
                return;
            }

            if (! $this->progress) {
                $this->progress = $this->output->createProgressBar();

                $this->progress->setFormat($this->format);

                $this->progress->setMessage($url, 'url');

                $this->progress->start();
            }

            if ($total) {
                $this->progress->setMaxSteps($total);
            }

            $this->progress->setProgress($loaded);

            if ($loaded >= $total) {
                $this->progress->finish();

                if ($this->printsNewLine) {
                    $this->output->newLine();
                }

                $this->finished = true;
                $this->progress = null;
            }
        });
    }
}
