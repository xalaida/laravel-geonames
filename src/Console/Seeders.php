<?php

namespace Nevadskiy\Geonames\Console;

use Illuminate\Console\Command;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LogLevel;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @mixin Command
 * TODO: refactor by extracting into factory (add feature tests to each console command before refactoring)
 * TODO: add possibility to indicate seeders dynamically in console command
 */
trait Seeders
{
    /**
     * Get the seeders list.
     * TODO: refactor using CompositeSeeder that resolves list automatically according to the config options.
     */
    protected function seeders(): array
    {
        $logger = $this->getLogger();

        return collect(config('geonames.seeders'))
            ->map(function ($seeder) use ($logger) {
                $seeder = resolve($seeder);

                // TODO: provide OutputAwareInterface

                if ($seeder instanceof LoggerAwareInterface) {
                    $seeder->setLogger($logger);
                }

                return $seeder;
            })
            ->all();
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
