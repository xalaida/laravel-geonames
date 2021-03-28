<?php

namespace Nevadskiy\Geonames\Tests\Feature;

use Illuminate\Foundation\Testing\WithFaker;
use Nevadskiy\Geonames\Geonames;
use Nevadskiy\Geonames\Models\Country;
use Nevadskiy\Geonames\Services\DownloadService;
use Nevadskiy\Geonames\Support\Cleaner\DirectoryCleaner;
use Nevadskiy\Geonames\Support\Geonames\FeatureCode;
use Nevadskiy\Geonames\Tests\DatabaseTestCase;
use Nevadskiy\Geonames\Tests\Support\Factories\CountryFactory;
use Nevadskiy\Geonames\Tests\Support\Utils\FakeDownloadService;
use Nevadskiy\Geonames\Tests\Support\Utils\FixtureFileBuilder;

class UpdateTest extends DatabaseTestCase
{
    use WithFaker;

    /**
     * Default configurations.
     *
     * @var array
     */
    protected $config = [
        'geonames.source' => DownloadService::SOURCE_SINGLE_COUNTRY,
        'geonames.filters.population' => 500,
        'geonames.filters.countries' => ['TS'],
        'geonames.translations' => true,
        'geonames.languages' => ['*'],
    ];

    // TODO: test that model is updated
    // TODO: test that new model is added
    // TODO: test that model is deleted

    // TODO: test that translation is updated
    // TODO: test that new translation is added
    // TODO: test that translation is deleted
    // TODO: test that directory is empty

    /** @test */
    public function it_can_update_database_from_daily_modification_files(): void
    {
        $country = CountryFactory::new()->create([
            'name' => 'Testing country (OLD)',
            'population' => 3232,
        ]);

        FakeDownloadService::new($this->app)
            ->countryInfo($this->createCountryInfoFile([
                [
                    'geonameid' => $country->geoname_id,
                    'Country' => 'Testing country (NEW)',
                    'ISO' => 'TS',
                ],
            ]))
            ->dailyModifications($this->createDailyModificationsFile([
                [
                    'geonameid' => $country->geoname_id,
                    'population' => 4545,
                ],
            ]))
            ->swap();

        $this->artisan('geonames:update');

        self::assertCount(1, Country::all());

        tap($country->fresh(), static function ($country) {
            self::assertEquals('Testing country (NEW)', $country->name);
            self::assertEquals(4545, $country->population);
        });
    }

    protected function defaultsGeonames(): array
    {
        return [
            'geonameid' => $this->faker->unique()->randomNumber(6),
            'name' => $this->faker->word,
            'asciiname' => $this->faker->word,
            'alternatenames' => '',
            'latitude' => $this->faker->randomFloat(),
            'longitude' => $this->faker->randomFloat(),
            'feature class' => $this->faker->randomElement(['A', 'P']),
            'feature code' => $this->faker->randomElement([FeatureCode::PPLC, FeatureCode::PCLI]),
            'country code' => $this->faker->countryCode,
            'cc2' => '',
            'admin1 code' => '',
            'admin2 code' => '',
            'admin3 code' => '',
            'admin4 code' => '',
            'population' => $this->faker->randomNumber(6),
            'elevation' => '',
            'dem' => '',
            'timezone' => $this->faker->timezone,
            'modification date' => $this->faker->date(),
        ];
    }

    protected function defaultsCountryInfo(): array
    {
        return [
            'ISO' => 'AE',
            'ISO3' => 'ARE',
            'ISO-Numeric' => '784',
            'fips' => 'AE',
            'Country' => 'United Arab Emirates',
            'Capital' => 'Abu Dhabi',
            'Area(in sq km)' => '82880',
            'Population' => '9630959',
            'Continent' => 'AS',
            'tld' => '.ae',
            'CurrencyCode' => 'AED',
            'CurrencyName' => 'Dirham',
            'Phone' => '',
            'Postal Code Format' => '',
            'Postal Code Regex' => '',
            'Languages' => 'ar-AE,fa,en,hi,ur',
            'geonameid' => '290557',
            'neighbours' => 'SA,OM',
            'EquivalentFipsCode' => '',
        ];
    }

    protected function buildTextTable(array $table, bool $headers = true, string $rowSeparator = "\n", string $colSeparator = "\t"): string
    {
        if ($headers) {
            // Prepare headers
            array_unshift($table, array_keys(reset($table)));
        }

        // Build content
        return implode($rowSeparator, array_map(static function ($row) use ($colSeparator) {
            return implode($colSeparator, $row);
        }, $table));
    }

    /**
     * Fake the directory cleaner.
     */
    protected function fakeDirectoryCleaner(): void
    {
        $directoryCleaner = $this->mock(DirectoryCleaner::class);

        $directoryCleaner->shouldReceive('keepGitignore')
            ->once()
            ->withNoArgs()
            ->andReturnSelf();

        $directoryCleaner->shouldReceive('clean')
            ->once()
            ->with(config('geonames.directory'));
    }

    protected function createDailyModificationsFile(array $data): string
    {
        $data = array_map(function ($row) {
            return array_merge($this->defaultsGeonames(), $row);
        }, $data);

        return app(FixtureFileBuilder::class)
            ->withHeaders()
            ->build('daily-modifications.txt', $data);
    }

    protected function createCountryInfoFile(array $data): string
    {
        $data = array_map(function ($row) {
            return array_merge($this->defaultsCountryInfo(), $row);
        }, $data);

        return app(FixtureFileBuilder::class)
            ->build('country-info.txt', $data);
    }
}
