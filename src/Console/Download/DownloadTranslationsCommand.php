<?php

namespace Nevadskiy\Geonames\Console\Download;

use Illuminate\Console\Command;
use Nevadskiy\Geonames\Support\FileDownloader\ConsoleFileDownloader;
use Nevadskiy\Geonames\Support\Unzipper\Unzipper;

class DownloadTranslationsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'geonames:download:translations';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Download the geonames translations dataset';

    /**
     * Execute the console command.
     */
    public function handle(ConsoleFileDownloader $downloader, Unzipper $unzipper): void
    {
        $this->unzip($unzipper, $this->download($downloader));
    }

    /**
     * Download the geonames resource dataset.
     *
     * @param ConsoleFileDownloader $downloader
     * @return string
     */
    private function download(ConsoleFileDownloader $downloader): string
    {
        return $downloader->enableProgressBar($this->getOutput())
            ->update()
            ->download($this->getUrl(), config('geonames.directory'));
    }

    /**
     * Unzip the downloaded resource.
     *
     * @param Unzipper $unzipper
     * @param string $path
     */
    private function unzip(Unzipper $unzipper, string $path): void
    {
        // TODO: refactor unzipper as downloader decorator
        $unzipper->extractIntoDirectory()->unzip($path);
    }

    /**
     * Get the geonames resource URL.
     *
     * @return string
     */
    private function getUrl(): string
    {
        return 'http://download.geonames.org/export/dump/alternateNames.zip';
    }
}