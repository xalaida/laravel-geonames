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
use Psr\Log\LogLevel;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @mixin Command
 */
trait Seeders
{
    /**
     * Get the seeders list.
     * TODO: refactor using CompositeSeeder that resolves list automatically according to the config options.
     */
    protected function seeders(): array
    {
        // TODO: allow to override existing files along with zip archive...

        $downloader = $this->getHistoryDownloader();
        $reader = $this->getReader();
        $logger = $this->getLogger();

        return collect(config('geonames.seeders'))
            ->map(function ($seeder) use ($downloader, $reader, $logger) {
                $seeder = resolve($seeder, [
                    'downloader' => $downloader,
                    'reader' => $reader,
                ]);

                // TODO: use LoggerAwareInterface
                if (method_exists($seeder, 'setLogger')) {
                    $seeder->setLogger($logger);
                }

                return $seeder;
            })
            ->all();
    }

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

        $downloader->overwrite(); // TODO: use this function with command option flag.

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
