<?php

namespace Nevadskiy\Geonames\Tests\Feature;

use Illuminate\Foundation\Testing\WithFaker;
use Nevadskiy\Geonames\Models\Country;
use Nevadskiy\Geonames\Services\DownloadService;
use Nevadskiy\Geonames\Tests\DatabaseTestCase;
use Nevadskiy\Geonames\Tests\Support\Factories\CountryFactory;
use Nevadskiy\Geonames\Tests\Support\Utils\FakeDownloadService;

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
        $country = CountryFactory::new()->create();

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

    /** @test */
    public function it_clears_directory_after_updating(): void
    {
        $directory = config('geonames.directory');

        FakeDownloadService::new($this->app)->dailyModifications([[]])->swap();

        self::assertDirectoryIsNotEmpty($directory);

        $this->artisan('geonames:update');

        self::assertDirectoryIsEmpty($directory);
    }

    /** @test */
    public function it_does_not_clear_directory_after_updating_if_option_is_specified(): void
    {
        $directory = config('geonames.directory');

        FakeDownloadService::new($this->app)->dailyModifications([[]])->swap();

        self::assertDirectoryIsNotEmpty($directory);

        $this->artisan('geonames:update', ['--keep-files' => true]);

        self::assertDirectoryIsNotEmpty($directory);
    }
}
