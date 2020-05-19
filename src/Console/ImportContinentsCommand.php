<?php

namespace Nevadskiy\Geonames\Console;

use Illuminate\Console\Command;
use Nevadskiy\Geonames\Models\Continent;

class ImportContinentsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'geonames:continents:import';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import continents dataset into database.';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        foreach ($this->getContinentsList() as $continent) {
            $this->saveContinent($continent);
        }

        $this->info('All continents have been imported.');
    }

    /**
     * Get the continents list.
     *
     * @return array
     */
    private function getContinentsList(): array
    {
        return require __DIR__ . '/../../resources/data/continents.php';
    }

    /**
     * Save the given continent.
     *
     * @param array $continent
     */
    private function saveContinent(array $continent): void
    {
        Continent::create([
            'name' => $continent['name'],
            'code' => $continent['code'],
            'latitude' => $continent['latitude'],
            'longitude' => $continent['longitude'],
            'population' => $continent['population'],
            'dem' => $continent['dem'],
            'geoname_id' => $continent['geoname_id'],
            'created_at' => $continent['modification date'],
            'updated_at' => $continent['modification date'],
        ]);
    }
}
