<?php

namespace Nevadskiy\Geonames\Tests\Feature;

use Nevadskiy\Geonames\Models\Country;
use Nevadskiy\Geonames\Services\DownloadService;
use Nevadskiy\Geonames\Support\Cleaner\DirectoryCleaner;
use Nevadskiy\Geonames\Tests\DatabaseTestCase;
use Nevadskiy\Geonames\Tests\Support\Factories\CountryFactory;

class UpdateTest extends DatabaseTestCase
{
    /**
     * Default configurations.
     *
     * @var array
     */
    protected $config = [
        'geonames.source' => DownloadService::SOURCE_SINGLE_COUNTRY,
        'geonames.filters.population' => 500,
        'geonames.filters.countries' => ['AE'],
        'geonames.translations' => true,
        'geonames.languages' => ['*'],
    ];

    /** @test */
    public function it_can_update_database_from_daily_modification_files(): void
    {
        $country = CountryFactory::new()->create([
            'geoname_id' => 290557,
            'name' => 'United Arab Emirates (OLD)'
        ]);

        $this->fakeDirectoryCleaner();
        $this->fakeDownloadService();

        $this->artisan('geonames:update');

        self::assertCount(1, Country::all());
        self::assertEquals('United Arab Emirates', $country->fresh()->name);
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

    /**
     * Fake the directory cleaner.
     */
    protected function fakeDownloadService(): void
    {
        $downloadService = $this->mock(DownloadService::class);

        $downloadService->shouldReceive('downloadCountryInfoFile')
            ->withNoArgs()
            ->andReturn($this->fixture('countryInfo.txt'));

        $downloadService->shouldReceive('downloadDailyModifications')
            ->withNoArgs()
            ->andReturn($this->fixture('dailyModifications.txt'));

        $downloadService->shouldReceive('downloadDailyDeletes')
            ->withNoArgs()
            ->andReturn($this->fixture('empty.txt'));

        $downloadService->shouldReceive('downloadDailyAlternateNamesModifications')
            ->withNoArgs()
            ->andReturn($this->fixture('empty.txt'));

        $downloadService->shouldReceive('downloadDailyAlternateNamesDeletes')
            ->withNoArgs()
            ->andReturn($this->fixture('empty.txt'));
    }
}
