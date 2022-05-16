<?php

namespace Nevadskiy\Geonames\Seeders;

use Illuminate\Support\LazyCollection;
use Nevadskiy\Geonames\Parsers\GeonamesParser;
use Nevadskiy\Geonames\Services\ContinentCodeGenerator;
use Nevadskiy\Geonames\Definitions\FeatureCode;
use Nevadskiy\Geonames\Services\DownloadService;

class ContinentSeeder extends ModelSeeder
{
    /**
     * The continent code generator instance.
     *
     * @var ContinentCodeGenerator
     */
    private $codeGenerator;

    /**
     * Make a new seeder instance.
     */
    public function __construct(ContinentCodeGenerator $codeGenerator)
    {
        $this->codeGenerator = $codeGenerator;
    }

    /**
     * Run the continent seeder.
     */
    public function seed(): void
    {
        foreach ($this->continents()->chunk(1000) as $continents) {
            $this->query()->insert($continents->all());
        }
    }

    /**
     * Get the continent records for seeding.
     */
    public function continents(): LazyCollection
    {
        // TODO: refactor downloading by passing Downloader instance from constructor.
        $path = resolve(DownloadService::class)->downloadAllCountries();

        return LazyCollection::make(function () use ($path) {
            foreach (resolve(GeonamesParser::class)->each($path) as $record) {
                if ($this->filter($record)) {
                    yield $this->mapRecord($record);
                }
            }
        });
    }

    /**
     * Determine if the given record should be seeded.
     */
    protected function filter(array $record): bool
    {
        return $record['feature code'] === FeatureCode::CONT;
    }

    /**
     * Map fields of the given record to the continent model attributes.
     */
    protected function mapFields(array $record): array
    {
        return [
            'name' => $record['name'],
            'code' => $this->codeGenerator->generate($record['name']),
            'latitude' => $record['latitude'],
            'longitude' => $record['longitude'],
            'timezone_id' => $record['timezone'],
            'population' => $record['population'],
            'dem' => $record['dem'],
            'feature_code' => $record['feature code'],
            'geoname_id' => $record['geonameid'],
            'synced_at' => $record['modification date'],
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
