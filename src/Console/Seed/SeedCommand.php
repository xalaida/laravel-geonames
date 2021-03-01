<?php

namespace Nevadskiy\Geonames\Console\Seed;

use Illuminate\Console\Command;

class SeedCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'geonames:seed';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Seed geonames into the database.';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $this->call('geonames:seed:continents');
        $this->call('geonames:seed:countries');
        $this->call('geonames:seed:divisions');
        $this->call('geonames:seed:cities');
        $this->call('geonames:seed:translations');
    }
}
