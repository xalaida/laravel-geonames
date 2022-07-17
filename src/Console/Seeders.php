<?php

namespace Nevadskiy\Geonames\Console;

use Illuminate\Console\Command;
use Nevadskiy\Downloader\CurlDownloader;
use Nevadskiy\Downloader\Downloader;
use Nevadskiy\Geonames\Downloader\ConsoleProgressDownloader;
use Nevadskiy\Geonames\Downloader\HistoryDownloader;
use Nevadskiy\Geonames\Downloader\UnzipDownloader;
use Nevadskiy\Geonames\Downloader\Unzipper;
use Nevadskiy\Geonames\Reader\ConsoleProgressReader;
use Nevadskiy\Geonames\Reader\FileReader;
use Nevadskiy\Geonames\Reader\Reader;
use Nevadskiy\Geonames\Services\DownloadService;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LogLevel;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @mixin Command
 */
trait Seeders
{
    protected function downloadService(Downloader $downloader)
    {
        // TODO: refactor this to use DI & container injection.

        if (app()->bound(DownloadService::class)) {
            return app()->get(DownloadService::class);
        }

        return resolve(DownloadService::class, [
            'downloader' => $downloader,
        ]);
    }

    /**
     * Get the seeders list.
     * TODO: refactor using CompositeSeeder that resolves list automatically according to the config options.
     */
    protected function seeders(): array
    {
        // TODO: allow to override existing files along with zip archive...
        // TODO: add possibility to rerun with already downloaded files (without --force option) and with overriding (with --force) option... (also provide --clean/--keep option)

        $downloader = $this->getDownloader();
        $reader = $this->getReader();
        $logger = $this->getLogger();

        return collect(config('geonames.seeders'))
            ->map(function ($seeder) use ($downloader, $reader, $logger) {
                $seeder = resolve($seeder, [
                    'downloadService' => $this->downloadService($downloader),
                    'reader' => $reader,
                ]);

                if ($seeder instanceof LoggerAwareInterface) {
                    $seeder->setLogger($logger);
                }

                return $seeder;
            })
            ->all();
    }

    private function getDownloader(): Downloader
    {
        // TODO: refactor this to use DI & container injection.

        if (app()->bound(Downloader::class)) {
            return app()->get(Downloader::class);
        }

        return $this->getHistoryDownloader();
    }

    /**
     * Get the history downloader.
     * It allows to prevent de-syncs when new geonames file is uploaded between already running seeder processes.
     */
    private function getHistoryDownloader(): Downloader
    {
        return new HistoryDownloader($this->getUnzipDownloader());
    }

    private function getReader(): Reader
    {
        return new ConsoleProgressReader(new FileReader(), $this->getOutput());
    }

    private function getUnzipDownloader(): Downloader
    {
        return new UnzipDownloader($this->consoleDownloader(), new Unzipper());
    }

    private function consoleDownloader(): Downloader
    {
        return new ConsoleProgressDownloader($this->curlDownloader(), $this->getOutput());
    }

    private function curlDownloader(): CurlDownloader
    {
        $downloader = new CurlDownloader();

        // $downloader->overwrite(); // TODO: use this function with command option flag.

        // $downloader->withoutClobbering();

        return $downloader;
    }

    /**
     * @TODO add stack logger that uses file log (resolve from config)
     * @return ConsoleLogger
     */
    private function getLogger(): ConsoleLogger
    {
        return new ConsoleLogger($this->getOutput(), [
            LogLevel::NOTICE => OutputInterface::VERBOSITY_NORMAL,
            LogLevel::INFO => OutputInterface::VERBOSITY_NORMAL,
        ]);
    }
}
