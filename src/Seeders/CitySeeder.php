<?php

namespace Nevadskiy\Geonames\Seeders;

use App\Models\Geo\City;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\LazyCollection;
use Nevadskiy\Geonames\Definitions\FeatureCode;
use Nevadskiy\Geonames\Parsers\GeonamesParser;
use Nevadskiy\Geonames\Services\DownloadService;
use Nevadskiy\Geonames\Support\Batch\Batch;

// TODO: delete files using trash class (add to trash files and clear afterwards)
// TODO: consider adding scanning DB table to use only that attributes
class CitySeeder
{
    /**
     * TODO: guess the default model name.
     * The continent model class.
     */
    protected static $model;

    /**
     * @var array
     */
    private $countries;

    /**
     * @var array
     */
    private $divisions;

    /**
     * Use the given city model class.
     */
    public static function useModel(string $model): void
    {
        static::$model = $model;
    }

    public static function getModel(): Model
    {
        // TODO: check if class exists and is a subclass of eloquent model

        return new static::$model;
    }

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

    /**
     * Sync database according to the geonames dataset.
     * TODO: add report
     */
    public function sync(): void
    {
        // TODO: what if division and cities were added at same time... (division can be deleted (do not use restrictOnDelete))
        // TODO: add logging here...

        $count = City::query()->count();
        $previouslySyncedAt = City::query()->max('synced_at');

        // TODO: think how to do it better (do not update 4 million rows at the same time)
        $this->prepareToSync();

        foreach ($this->cities()->chunk(1000) as $cities) {
            // TODO: compile this update fields automatically from wildcard and exclude geoname_id and created_at
            City::query()->upsert($cities->all(), ['geoname_id'], [
                'name',
                'country_id',
                'division_id',
                'latitude',
                'longitude',
                'timezone_id',
                'population',
                'elevation',
                'dem',
                'feature_code',
                'synced_at',
                'updated_at', // added automatically
            ]);
        }

        $created = City::query()->count() - $count;
        $updated = City::query()->whereDate('synced_at', '>', $previouslySyncedAt)->count();
        // TODO: add possibility to prevent models from being deleted... (probably use extended query with some scopes)
        // Delete can be danger here because empty file with destroy every record... also there is hard to delete every single record one be one... soft delete?
        $deleted = City::query()->whereNull('synced_at')->delete();

        dump("Created: {$created}");
        dump("Updated: {$updated}");
        dump("Deleted: {$deleted}");
    }

    public function truncate()
    {
        $this->query()->truncate();
    }

    private function query(): Builder
    {
        return static::getModel()->newQuery();
    }

    public function records(): LazyCollection
    {
        // $path = resolve(DownloadService::class)->downloadAllCountries();
        $geonamesParser = app(GeonamesParser::class);

        $path = '/var/www/html/storage/meta/geonames/allCountries.txt';

        return new LazyCollection(function () use ($geonamesParser, $path) {
            foreach ($geonamesParser->each($path) as $record) {
                if ($this->shouldSeed($record)) {
                    yield $record;
                }
            }
        });
    }

    /**
     * Get city records to insert.
     */
    public function cities(): LazyCollection
    {
        // TODO: consider loading dependencies locally here.
        $this->load();

        return LazyCollection::make(function () {
            foreach ($this->records() as $record) {
                yield $this->map($record);
            }
        });

        // TODO: unset loaded dependencies, or better to load them locally.
    }

    protected function load(): void
    {
        $this->loadCountries();
        $this->loadDivisions();
    }

    protected function loadCountries(): void
    {
        $this->countries = CountrySeeder::getModel()
            ->newQuery()
            ->pluck('id', 'code')
            ->all();
    }

    protected function loadDivisions(): void
    {
        $this->divisions = DivisionSeeder::getModel()
            ->newQuery()
            ->get(['id', 'country_id', 'code'])
            ->groupBy(['country_id', 'code'])
            ->toArray();
    }

    /**
     * Determine if the given record should be seeded.
     */
    protected function shouldSeed(array $record): bool
    {
        // TODO: add filter by population.
        // TODO: add possibility to use different feature codes.

        return collect($this->featureCodes())->contains($record['feature code']);
    }

    /**
     * Get the list of feature codes of a country.
     */
    protected function featureCodes(): array
    {
        return [
            FeatureCode::PPL,
            FeatureCode::PPLC,
            FeatureCode::PPLA,
            FeatureCode::PPLA2,
            FeatureCode::PPLA3,
            FeatureCode::PPLX,
            FeatureCode::PPLG,
        ];
    }

    protected function mapRecord(array $record): array
    {
        return static::getModel()
            ->forceFill($this->map($record))
            ->getAttributes();
    }

    /**
     * Map fields of the given record to the continent model attributes.
     * TODO: add possibility to specify updatable attributes separately...
     */
    protected function map(array $record): array
    {
        return [
            'name' => $record['asciiname'] ?: $record['name'],
            'country_id' => $this->getCountryId($record),
            'division_id' => $this->getDivisionId($record),
            'latitude' => $record['latitude'],
            'longitude' => $record['longitude'],
            'timezone_id' => $record['timezone'],
            'population' => $record['population'],
            'elevation' => $record['elevation'],
            'dem' => $record['dem'],
            'feature_code' => $record['feature code'],
            'geoname_id' => $record['geonameid'],
            'synced_at' => $record['modification date'],
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    /**
     * Get a country ID by the given record.
     */
    protected function getCountryId(array $record): string
    {
        return $this->countries[$record['country code']];
    }

    /**
     * Get a division ID by the given record.
     */
    protected function getDivisionId(array $record): ?string
    {
        return $this->divisions[$this->getCountryId($record)][$record['admin1 code']][0]['id'] ?? null;
    }

    /**
     * @return void
     */
    protected function prepareToSync(): void
    {
        $this->nullifySyncedAtTimestamp();
    }

    /**
     * @return void
     */
    protected function nullifySyncedAtTimestamp(): void
    {
        while (City::query()->whereNotNull('synced_at')->exists()) {
            dump('nullifying...');

            City::query()
                ->toBase()
                ->limit(50_000)
                ->update(['synced_at' => null]);
        }
    }
}
