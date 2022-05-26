<?php

namespace Nevadskiy\Geonames\Console;

use Illuminate\Console\Command;
use Psr\Log\LogLevel;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;

class GeonamesSeedCommand extends Command
{
    /**
     * The name and signature of the console command.
     * TODO: add description to options
     * TODO: rewrite keep files to clean files.
     *
     * @var string
     */
    protected $signature = 'geonames:seed {--truncate} {--keep-files}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Seed the geonames dataset into the database.';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $seeders = $this->seeders();

        if ($this->option('truncate')) {
            $this->truncate($seeders);
        }

        // TODO: do not import locales: wkdt, post, link, ...
        // TODO: build console logger and set it from here like this:
        // TODO: add decorator for downloader that captures all downloaded files and allow the possibility to delete them after.
        // TODO: do not import locales: wkdt, post, link, ...
        // TODO: configure donwloader to reuse existing file even when remote size is different

        /*
         * function handle(Parser $parser)
         * {
         *   $parser->setLogger($this->consoleLogger())
         *   $parser->setProgress($this->consoleProgress())
         * }
         */

        $this->seed($seeders);
    }

    /**
     * Truncate tables using given seeders.
     */
    private function truncate(array $seeders): void
    {
        // TODO: add confirmation
        // TODO: add success message

        foreach (array_reverse($seeders) as $seeder) {
            $seeder->truncate();
        }
    }

    /**
     * Seed the dataset using given seeders.
     */
    private function seed(array $seeders): void
    {
        foreach ($seeders as $seeder) {
            $seeder->seed();
        }
    }

    /**
     * Get the seeders list.
     * TODO: refactor using CompositeSeeder that resolves list automatically according to the config options.
     */
    protected function seeders(): array
    {
        return collect(config('geonames.seeders'))
            ->map(function ($seeder) {
                $seeder = resolve($seeder);

                if (method_exists($seeder, 'setLogger')) {
                    // TODO: add stack logger that uses file log (resolve from config)
                    $seeder->setLogger(new ConsoleLogger($this->getOutput(), [
                        LogLevel::NOTICE => OutputInterface::VERBOSITY_NORMAL,
                        LogLevel::INFO => OutputInterface::VERBOSITY_NORMAL,
                    ]));
                }

                return $seeder;
            })
            ->all();
    }
}
