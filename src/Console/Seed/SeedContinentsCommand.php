<?php

namespace Nevadskiy\Geonames\Console\Seed;

use Illuminate\Console\Command;
use Illuminate\Contracts\Events\Dispatcher;
use Nevadskiy\Geonames\Console\Seed\Traits\Truncate;
use Nevadskiy\Geonames\Events\GeonamesCommandReady;
use Nevadskiy\Geonames\Models\Continent;
use Nevadskiy\Geonames\Seeders\ContinentSeeder;
use RuntimeException;

class SeedContinentsCommand extends Command
{
    use Truncate;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'geonames:seed:continents {--source=} {--truncate}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Seed continents into the database.';

    /**
     * Execute the console command.
     */
    public function handle(Dispatcher $dispatcher, ContinentSeeder $seeder): void
    {
        $this->info('Start seeding continents.');

        $dispatcher->dispatch(new GeonamesCommandReady());

        $this->truncateAttempt();

        foreach ($this->continents() as $id => $continent) {
            $seeder->seed($continent, $id);
        }

        $this->info('Continents have been successfully seeded.');
    }

    /**
     * Get a table name to be truncated.
     */
    protected function getTableToTruncate(): string
    {
        return Continent::TABLE;
    }

    /**
     * Get continents for seeding.
     */
    protected function continents(): array
    {
        return require $this->getContinentsSourcePath();
    }

    /**
     * Get continents source path.
     *
     * @return string
     */
    protected function getContinentsSourcePath(): string
    {
        if ($this->hasOptionSourcePath()) {
            return $this->getOptionSourcePath();
        }

        $publishedPath = $this->getPublishedSourcePath();

        if (file_exists($publishedPath)) {
            return $publishedPath;
        }

        return $this->getDefaultSourcePath();
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

    /**
     * Get the published continents source path.
     *
     * @return string
     */
    protected function getPublishedSourcePath(): string
    {
        return config('geonames.directory') . DIRECTORY_SEPARATOR . config('geonames.files.continents');
    }

    /**
     * Get the default continents source path.
     *
     * @return string
     */
    protected function getDefaultSourcePath(): string
    {
        return __DIR__.'/../../../resources/meta/continents.php';
    }
}
