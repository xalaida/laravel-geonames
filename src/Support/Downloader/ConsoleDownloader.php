<?php

namespace Nevadskiy\Geonames\Support\Downloader;

use Illuminate\Console\OutputStyle;
use Symfony\Component\Console\Helper\ProgressBar;

class ConsoleDownloader implements Downloader
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
    public function __construct(Downloader $downloader, OutputStyle $output)
    {
        $this->downloader = $downloader;
        $this->withProgressBar($output);
    }

    /**
     * Enable the console progress bar.
     *
     * @return ConsoleDownloader
     */
    public function withProgressBar(OutputStyle $output): self
    {
        $this->onReady(function (int $steps, string $url) use ($output) {
            $this->progress = $output->createProgressBar($steps);

            if ($steps) {
                $this->progress->setFormat(
                    "<options=bold;fg=green>Downloading:</> {$url}\n".
                    "%bar% %percent%%\n".
                    "<fg=blue>Remaining Time:</> %remaining%\n"
                );
            }
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
     * {@inheritdoc}
     */
    public function download(string $url, string $directory, string $name = null)
    {
        return $this->downloader->download($url, $directory, $name);
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
