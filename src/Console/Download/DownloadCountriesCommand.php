<?php

namespace Nevadskiy\Geonames\Console\Download;

use Illuminate\Console\Command;
use Nevadskiy\Geonames\Support\FileDownloader\ConsoleFileDownloader;
use Nevadskiy\Geonames\Support\Unzipper\Unzipper;

class DownloadCountriesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'geonames:download:countries';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch the countries dataset';

    /**
     * Execute the console command.
     */
    public function handle(ConsoleFileDownloader $downloader, Unzipper $unzipper): void
    {
        // TODO: parameters.
        $url = 'http://download.geonames.org/export/dump/UA.zip';
        $directory = config('geonames.directory');

        $path = $downloader->enableProgressBar($this->getOutput())->update()->download($url, $directory);

        // TODO: refactor unzipper as downloader decorator
        $unzipper->extractIntoDirectory()->unzip($path);

        $this->info('Countries resource have been downloaded.');
    }
}
