<?php

namespace Nevadskiy\Geonames\Support\FileDownloader;

use Illuminate\Console\OutputStyle;

class ConsoleFileDownloader extends FileDownloader
{
    /**
     * ConsoleFileDownloader constructor.
     *
     * @param OutputStyle $output
     * @param int $bufferSize
     */
    public function __construct(OutputStyle $output, int $bufferSize = 1024 * 1024)
    {
        parent::__construct($bufferSize);
        $this->attachConsoleProgressBar($output);
    }

    /**
     * Attach the console progress bar.
     *
     * @param OutputStyle $output
     */
    protected function attachConsoleProgressBar(OutputStyle $output): void
    {
        $this->onReady(function (int $steps, string $url) use ($output) {
            $progress = $output->createProgressBar($steps);
            $progress->setFormat("<info>Downloading:</info> {$url}\n%bar% %percent%%\n<info>Remaining Time:</info> %remaining%");

            $this->onStep(static function () use ($progress) {
                $progress->advance();
            });

            $this->onFinish(static function (string $path) use ($progress, $output) {
                $progress->finish();
                $output->newLine();
                $output->writeln("<info>File Downloaded:</info> {$path}");
            });
        });
    }
}
