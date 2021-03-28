<?php

namespace Nevadskiy\Geonames\Tests\Feature;

use Illuminate\Foundation\Testing\WithFaker;
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

    // TODO: test that translation is updated
    // TODO: test that new translation is added
    // TODO: test that translation is deleted

    // TODO: test that directory is empty

    /** @test */
    public function it_can_update_country_from_daily_modification_files(): void
    {
        $country = CountryFactory::new()->create([
            'name' => 'Testing country (OLD)',
            'population' => 3232,
        ]);

        FakeDownloadService::new($this->app)
            ->countryInfo([
                [
                    'geonameid' => $country->geoname_id,
                    'Country' => 'Testing country (NEW)',
                    'ISO' => 'TS',
                ],
            ])
            ->dailyModifications([
                [
                    'geonameid' => $country->geoname_id,
                    'population' => 4545,
                ],
            ])
            ->swap();

        $this->artisan('geonames:update');

        self::assertCount(1, Country::all());

        tap($country->fresh(), static function ($country) {
            self::assertEquals('Testing country (NEW)', $country->name);
            self::assertEquals(4545, $country->population);
        });
    }

    /** @test */
    public function it_can_delete_country_from_daily_modification_files(): void
    {
        $country = CountryFactory::new()->create([
            'name' => 'Testing country',
        ]);

        FakeDownloadService::new($this->app)
            ->countryInfo([
                [
                    'geonameid' => $country->geoname_id,
                    'ISO' => 'NO',
                ],
            ])
            ->dailyModifications([
                [
                    'geonameid' => $country->geoname_id,
                ],
            ])
            ->swap();

        self::assertCount(1, Country::all());

        $this->artisan('geonames:update');

        self::assertEmpty(Country::all());
    }

    /** @test */
    public function it_can_delete_city_from_daily_deletes_files(): void
    {
        $country = CountryFactory::new()->create();

        FakeDownloadService::new($this->app)
            ->dailyDeletes([
                [
                    'geonameid' => $country->geoname_id,
                ],
            ])
            ->swap();

        self::assertCount(1, Country::all());

        $this->artisan('geonames:update');

        self::assertEmpty(Country::all());
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
}
