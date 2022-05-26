<?php

namespace Nevadskiy\Geonames\Console;

use Illuminate\Console\Command;
use Nevadskiy\Geonames\Support\Cleaner\DirectoryCleaner;
use Psr\Log\LogLevel;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;

class GeonamesSeedCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'geonames:seed
                            {--truncate : Whether the table should be truncated before seeding}
                            {--clean : Whether the directory with geonames downloads should be cleaned}';

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

        $this->truncate($seeders);

        $this->seed($seeders);

        $this->clean();
    }

    /**
     * Truncate tables using given seeders.
     *
     * TODO: add prod confirmation.
     */
    private function truncate(array $seeders): void
    {
        if ($this->option('truncate')) {
            foreach (array_reverse($seeders) as $seeder) {
                $seeder->truncate();
            }
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
     * Clean the geonames downloads directory.
     */
    private function clean(): void
    {
        if ($this->option('clean')) {
            // TODO: pass logger inside.
            (new DirectoryCleaner())->clean(config('geonames.directory'));
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
