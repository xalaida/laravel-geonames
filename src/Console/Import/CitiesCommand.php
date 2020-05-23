<?php

namespace Nevadskiy\Geonames\Console\Import;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Nevadskiy\Geonames\Models\City;
use Nevadskiy\Geonames\Models\Country;
use Nevadskiy\Geonames\Models\Timezone;
use Nevadskiy\Geonames\Parsers\GeonamesParser;
use Nevadskiy\Geonames\Support\Bulker;
use Nevadskiy\Geonames\Support\Timer;

class CitiesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'geonames:import:cities';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import cities dataset into database.';

    /**
     * @var GeonamesParser
     */
    private $geonamesParser;

    /**
     * @var Country[]
     */
    private $countries;

    /**
     * @var Timezone[]
     */
    private $timezones;

    /**
     * CitiesCommand constructor.
     *
     * @param GeonamesParser $geonamesParser
     */
    public function __construct(GeonamesParser $geonamesParser)
    {
        parent::__construct();
        $this->geonamesParser = $geonamesParser;
    }

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $seconds = Timer::new()->measure(function () {
            $this->importCities();
        });

        $this->info("All cities have been imported in {$seconds} seconds.");
    }

    /**
     * Import cities into database.
     */
    private function importCities(): void
    {
        $this->loadRelations();

        $bulker = new Bulker(function ($cities) {
            DB::table(City::TABLE)->insert($cities);
        }, 1000);

        foreach ($this->geonamesParser->each() as $geoname) {
            if ($geoname['feature class'] === 'P') {
                $bulker->push($this->getCityData($geoname));
            }
        }

        $bulker->commit();
    }

    /**
     * Load relations data.
     */
    private function loadRelations(): void
    {
        $this->loadCountries();
        $this->loadTimezones();
    }

    /**
     * Load countries data.
     */
    private function loadCountries(): void
    {
        $this->countries = Country::all()->mapWithKeys(function (Country $country) {
            return [$country->iso => $country];
        });
    }

    /**
     * Load timezones data.
     */
    private function loadTimezones(): void
    {
        $this->timezones = Timezone::all()->mapWithKeys(function (Timezone $timezone) {
            return [$timezone->name => $timezone];
        });
    }

    /**
     * Get a city data from the geoname.
     *
     * @param array $geoname
     * @return array
     */
    private function getCityData(array $geoname): array
    {
        return [
            'id' => City::generateKey(),
            'name' => $geoname['name'],
            'name_ascii' => $geoname['asciiname'],
            'country_id' => $this->countries[$geoname['country code']]->id,
            'latitude' => $geoname['latitude'],
            'longitude' => $geoname['longitude'],
            'population' => $geoname['population'],
            'elevation' => $geoname['elevation'],
            'dem' => $geoname['dem'],
            'timezone_id' => $this->timezones[$geoname['timezone']]->id,
            'feature_code' => $geoname['feature code'],
            'geoname_id' => $geoname['geonameid'],
            'created_at' => $geoname['modification date'],
            'updated_at' => $geoname['modification date'],
        ];
    }
}
