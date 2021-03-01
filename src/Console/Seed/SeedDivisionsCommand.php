<?php

namespace Nevadskiy\Geonames\Console\Seed;

use Generator;
use Illuminate\Console\Command;
use Illuminate\Contracts\Events\Dispatcher;
use Nevadskiy\Geonames\Console\Seed\Traits\Truncate;
use Nevadskiy\Geonames\Events\GeonamesCommandReady;
use Nevadskiy\Geonames\Models\Division;
use Nevadskiy\Geonames\Parsers\GeonamesParser;
use Nevadskiy\Geonames\Seeders\DivisionSeeder;
use RuntimeException;

class SeedDivisionsCommand extends Command
{
    use Truncate;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'geonames:seed:divisions {--source=} {--truncate}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Seed divisions into the database.';

    /**
     * Execute the console command.
     */
    public function handle(Dispatcher $dispatcher, GeonamesParser $parser, DivisionSeeder $seeder): void
    {
        $this->info('Start seeding divisions. It may take some time.');

        $dispatcher->dispatch(new GeonamesCommandReady());

        $this->truncateAttempt();

        $this->setUpProgressBar($parser);

        foreach ($this->divisions($parser) as $id => $division) {
            $seeder->seed($division, $id);
        }

        $this->info('Divisions have been successfully seeded.');
    }

    /**
     * Get a table name to be truncated.
     */
    protected function getTableToTruncate(): string
    {
        return Division::TABLE;
    }

    /**
     * TODO: extract into parser decorator ProgressBarParser.php
     * Set up the progress bar.
     */
    private function setUpProgressBar(GeonamesParser $parser, int $step = 1000): void
    {
        $progress = $this->output->createProgressBar();

        $parser->enableCountingLines()
            ->onReady(static function (int $linesCount) use ($progress) {
                $progress->start($linesCount);
            })
            ->onEach(static function () use ($progress, $step) {
                $progress->advance($step);
            }, $step)
            ->onFinish(function () use ($progress) {
                $progress->finish();
                $this->output->newLine();
            });
    }

    /**
     * Get divisions for seeding.
     */
    private function divisions(GeonamesParser $parser): Generator
    {
        return $parser->forEach($this->getDivisionsSourcePath());
    }

    /**
     * Get divisions source path.
     */
    protected function getDivisionsSourcePath(): string
    {
        if ($this->hasOptionSourcePath()) {
            return $this->getOptionSourcePath();
        }

        return config('geonames.directory') . DIRECTORY_SEPARATOR . config('geonames.files.all_countries');
    }

    /**
     * Determine whether the command has given source option.
     *
     * @return bool
     */
    protected function hasOptionSourcePath(): bool
    {
        return (bool) $this->option('source');
    }

    /**
     * Get source path from the command option.
     *
     * @return string
     */
    public function getOptionSourcePath(): string
    {
        $path = base_path($this->option('source'));

        if (! file_exists($path)) {
            throw new RuntimeException("File does not exist {$path}.");
        }

        return $path;
    }
}
