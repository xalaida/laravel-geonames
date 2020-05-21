<?php

namespace Nevadskiy\Geonames\Console\Import;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Nevadskiy\Geonames\Models\Country;
use Nevadskiy\Geonames\Models\Timezone;
use Nevadskiy\Geonames\Parsers\TimezonesParser;

class TimezonesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'geonames:import:timezones';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import timezones dataset into database.';

    /**
     * Execute the console command.
     */
    public function handle(TimezonesParser $parser): void
    {
        foreach ($parser->each() as $timezone) {
            $this->saveTimezone($timezone);
        }

        $this->info('All timezones have been imported.');
    }

    /**
     * @param $timezone
     */
    private function saveTimezone($timezone): void
    {
        Timezone::create([
            'country_id' => Country::firstWhere('iso', $timezone['CountryCode'])->id,
            'name' => $timezone['TimeZoneId'],
            'offset_gmt' => $timezone['GMT offset 1. Jan 2020'],
            'offset_dst' => $timezone['DST offset 1. Jul 2020'],
            'offset_raw' => $timezone,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
    }
}
