<?php

namespace Nevadskiy\Geonames\Seeders;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Nevadskiy\Geonames\Parsers\AlternateNameParser;
use Nevadskiy\Geonames\Support\Batch\Batch;

class CountryTranslationsSeeder
{
    /**
     * @var array
     */
    private $countries;

//    /**
//     * Use the given city model class.
//     */
//    public static function useModel(string $model): void
//    {
//        static::$model = $model;
//    }

//    public static function getModel(): Model
//    {
//        // TODO: check if class exists and is a subclass of eloquent model
//
//        // return new static::$model;
//    }

    /**
     * Run the continent seeder.
     */
    public function seed(): void
    {
        $this->load();

        $batch = new Batch(function (array $records){
            $this->query()->insert($records);
        }, 1000);

        foreach ($this->records() as $division) {
             $batch->push($division);
        }

        $batch->commit();
    }

    public function truncate()
    {
        $this->query()->truncate();
    }

    private function query(): Builder
    {
        return DB::table('country_translations');

        // return static::getModel()->newQuery();
    }

    public function records(): iterable
    {
        // $path = resolve(DownloadService::class)->downloadAlternateNames();
        $path = '/var/www/html/storage/meta/geonames/alternateNames.txt';

        $parser = app(AlternateNameParser::class);

        foreach ($parser->each($path) as $record) {
            if ($this->shouldSeed($record)) {
                yield $this->map($record);
            }
        }
    }

    protected function load(): void
    {
        $this->loadCountries();
    }

    protected function loadCountries(): void
    {
        $this->countries = CountrySeeder::getModel()
            ->newQuery()
            ->pluck('id', 'geoname_id')
            ->all();
    }

    /**
     * Determine if the given record should be seeded.
     */
    protected function shouldSeed(array $record): bool
    {
        return isset($this->countries[$record['geonameid']]);
    }

    /**
     * Map fields of the given record to the model attributes.
     */
    protected function map(array $record): array
    {
        // TODO: think about processing using model (allows using casts and mutators)

        return [
            'country_id' => $this->countries[$record['geonameid']],
            'name' => $record['alternate name'],
            'is_preferred' => $record['isPreferredName'],
            'is_short' => $record['isShortName'],
            'is_colloquial' => $record['isColloquial'],
            'is_historic' => $record['isHistoric'],
            'geoname_id' => $record['geonameid'],
            'locale' => $record['isolanguage'],
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
