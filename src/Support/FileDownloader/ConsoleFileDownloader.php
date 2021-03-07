<?php

namespace Nevadskiy\Geonames\Support\FileDownloader;

use Illuminate\Console\OutputStyle;
use Symfony\Component\Console\Helper\ProgressBar;

class ConsoleFileDownloader implements Downloader
{
    /**
     * The decorated downloader instance.
     *
     * @var Downloader
     */
    protected $downloader;

    /**
     * The console progress bar.
     *
     * @var ProgressBar
     */
    protected $progress;

    /**
     * ConsoleFileDownloader constructor.
     */
    public function __construct(Downloader $downloader)
    {
        $this->downloader = $downloader;
    }

    /**
     * Enable the console progress bar.
     *
     * @param OutputStyle $output
     * @return ConsoleFileDownloader
     */
    public function withProgressBar(OutputStyle $output): self
    {
        $this->onReady(function (int $steps, string $url) use ($output) {
            $this->progress = $output->createProgressBar($steps);
            $this->progress->setFormat("<info>Downloading:</info> {$url}\n%bar% %percent%%\n<info>Remaining Time:</info> %remaining%");

        });

        $this->onStep(function () {
            $this->progress->advance();
        });

        $this->onFinish(function (string $path) use ($output) {
            $this->progress->finish();
            $output->newLine();
            $output->writeln("<info>File Downloaded:</info> {$path}");
        });

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function download(string $url, string $directory, string $name = null)
    {
        return $this->downloader->download($url, $directory, $name);
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
