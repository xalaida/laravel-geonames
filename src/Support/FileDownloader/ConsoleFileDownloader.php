<?php

namespace Nevadskiy\Geonames\Support\FileDownloader;

use Illuminate\Console\OutputStyle;
use Symfony\Component\Console\Helper\ProgressBar;

class ConsoleFileDownloader extends FileDownloader
{
    /**
     * The console progress bar.
     *
     * @var ProgressBar
     */
    protected $progress;

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
}
