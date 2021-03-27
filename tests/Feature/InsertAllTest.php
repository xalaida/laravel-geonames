<?php

namespace Nevadskiy\Geonames\Tests\Feature;

use Illuminate\Foundation\Application;
use Nevadskiy\Geonames\Models\City;
use Nevadskiy\Geonames\Models\Continent;
use Nevadskiy\Geonames\Models\Country;
use Nevadskiy\Geonames\Models\Division;
use Nevadskiy\Geonames\Services\DownloadService;
use Nevadskiy\Geonames\Support\Cleaner\DirectoryCleaner;
use Nevadskiy\Geonames\Tests\Support\Factories\ContinentFactory;
use Nevadskiy\Geonames\Tests\TestCase;
use Nevadskiy\Translatable\Models\Translation;

class InsertAllTest extends TestCase
{
    /**
     * Define environment setup.
     *
     * @param Application $app
     */
    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('geonames.source', DownloadService::SOURCE_ALL_COUNTRIES);
        $app['config']->set('geonames.filters.population', 500);
        $app['config']->set('geonames.filters.countries', ['*']);
        $app['config']->set('geonames.translations', true);
        $app['config']->set('geonames.languages', ['*']);

        parent::getEnvironmentSetUp($app);
    }

    /** @test */
    public function it_can_insert_geonames_dataset_into_database(): void
    {
        $this->fakeDownloadService();
        $this->fakeDirectoryCleaner();

        $this->migrate();
        $this->artisan('geonames:insert');

        self::assertCount(3, Continent::all());
        self::assertCount(1, Country::all());
        self::assertCount(1, Division::all());
        self::assertCount(1, City::all());
        self::assertCount(3, Translation::all());
    }

    /** @test */
    public function it_can_reset_tables_during_insert_process(): void
    {
        $this->fakeDownloadService();
        $this->fakeDirectoryCleaner();
        $this->migrate();

        $continent = ContinentFactory::new()->create();

        $this->artisan('geonames:insert', ['--reset' => true]);

        self::assertFalse(Continent::all()->contains($continent));
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
     * Fake download service.
     */
    protected function fakeDownloadService(): void
    {
        $downloadService = $this->mock(DownloadService::class);

        $downloadService->shouldReceive('downloadCountryInfoFile')
            ->withNoArgs()
            ->andReturn($this->fixture('countryInfo.txt'));

        $downloadService->shouldReceive('downloadSourceFiles')
            ->withNoArgs()
            ->andReturn([$this->fixture('allCountries.txt')]);

        $downloadService->shouldReceive('downloaderAlternateNames')
            ->withNoArgs()
            ->andReturn([$this->fixture('alternateNames.txt')]);
    }
}
