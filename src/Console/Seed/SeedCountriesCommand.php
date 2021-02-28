<?php

namespace Nevadskiy\Geonames\Console\Seed;

use Illuminate\Console\Command;
use Nevadskiy\Geonames\Models\Country;
use Nevadskiy\Geonames\Seeders\CountrySeeder;
use RuntimeException;

class SeedCountriesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'geonames:seed:countries {--source=} {--truncate}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Seed countries into the database.';

    /**
     * Execute the console command.
     */
    public function handle(CountrySeeder $seeder): void
    {
        $this->info('Start seeding countries.');

        $this->truncate();

        foreach ($this->countries() as $id => $country) {
            $seeder->seed($country, $id);
        }

        $this->info('Countries have been successfully seeded.');
    }

    /**
     * Truncate a table if option is specified.
     */
    private function truncate(): void
    {
        // TODO: add production warning

        if ($this->option('truncate')) {
            Country::query()->truncate();
            $this->info('Countries table has been truncated.');
        }
    }

    /**
     * Get countries for seeding.
     */
    protected function countries(): array
    {
        return require $this->getCountriesSourcePath();
    }

    /**
     * Get countries source path.
     *
     * @return string
     */
    protected function getCountriesSourcePath(): string
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
     * Get the published countries source path.
     *
     * @return string
     */
    protected function getPublishedSourcePath(): string
    {
        return config('geonames.directory') . DIRECTORY_SEPARATOR . config('geonames.files.countries');
    }

    /**
     * Get the default countries source path.
     *
     * @return string
     */
    protected function getDefaultSourcePath(): string
    {
        return __DIR__.'/../../../resources/meta/countries.php';
    }
}
