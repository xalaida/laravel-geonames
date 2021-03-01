<?php

namespace Nevadskiy\Geonames\Console\Seed;

use Generator;
use Illuminate\Console\Command;
use Illuminate\Contracts\Events\Dispatcher;
use Nevadskiy\Geonames\Console\Seed\Traits\Truncate;
use Nevadskiy\Geonames\Events\GeonamesCommandReady;
use Nevadskiy\Geonames\Parsers\AlternateNameParser;
use Nevadskiy\Geonames\Seeders\Translations\TranslationSeeder;
use Nevadskiy\Translatable\Models\Translation;
use RuntimeException;

class SeedTranslationsCommand extends Command
{
    use Truncate;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'geonames:seed:translations {--source=} {--truncate}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Seed translations into the database.';

    /**
     * Execute the console command.
     */
    public function handle(Dispatcher $dispatcher, AlternateNameParser $parser, TranslationSeeder $seeder): void
    {
        $this->info('Start seeding translations. It may take some time.');

        $dispatcher->dispatch(new GeonamesCommandReady());

        $this->truncateAttempt();

        $this->setUpProgressBar($parser);

        foreach ($this->translations($parser) as $id => $translation) {
            $seeder->seed($translation, $id);
        }

        $this->info('Translations have been successfully seeded.');
    }

    /**
     * Get a table name to be truncated.
     */
    protected function getTableToTruncate(): string
    {
        return app(Translation::class)->getTable();
    }

    /**
     * TODO: extract into parser decorator ProgressBarParser.php
     * Set up the progress bar.
     */
    private function setUpProgressBar(AlternateNameParser $parser, int $step = 1000): void
    {
        $progress = $this->output->createProgressBar();
        $progress->setFormat('very_verbose');

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
     * Get translations for seeding.
     */
    private function translations(AlternateNameParser $parser): Generator
    {
        return $parser->forEach($this->getTranslationsSourcePath());
    }

    /**
     * Get translations source path.
     */
    protected function getTranslationsSourcePath(): string
    {
        if ($this->hasOptionSourcePath()) {
            return $this->getOptionSourcePath();
        }

        return config('geonames.directory') . DIRECTORY_SEPARATOR . config('geonames.files.alternate_names');
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
