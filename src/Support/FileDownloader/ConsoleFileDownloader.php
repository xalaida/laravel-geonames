<?php

namespace Nevadskiy\Geonames\Support\FileDownloader;

use Illuminate\Console\OutputStyle;

class ConsoleFileDownloader extends FileDownloader
{
    /**
     * Enable the console progress bar.
     *
     * @param OutputStyle $output
     * @return ConsoleFileDownloader
     */
    public function enableProgressBar(OutputStyle $output): self
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

        return $this;
    }
}
