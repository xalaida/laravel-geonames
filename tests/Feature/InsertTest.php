<?php

namespace Nevadskiy\Geonames\Tests\Feature;

use Nevadskiy\Geonames\Models\City;
use Nevadskiy\Geonames\Models\Continent;
use Nevadskiy\Geonames\Models\Country;
use Nevadskiy\Geonames\Models\Division;
use Nevadskiy\Geonames\Services\DownloadService;
use Nevadskiy\Geonames\Support\Cleaner\DirectoryCleaner;
use Nevadskiy\Geonames\Tests\TestCase;
use Nevadskiy\Translatable\Models\Translation;

class InsertTest extends TestCase
{
    /** @test */
    public function it_can_insert_geonames_dataset_into_database(): void
    {
        $this->fakeDownloadService();

        $this->fakeDirectoryCleaner();

        $this->artisan('geonames:insert');

        self::assertCount(3, Continent::all());
        self::assertCount(1, Country::all());
        self::assertCount(1, Division::all());
        self::assertCount(1, City::all());
        self::assertCount(2, Translation::all());
    }

    protected function fakeDirectoryCleaner(): void
    {
        $directoryCleaner = $this->mock(DirectoryCleaner::class);

        $directoryCleaner->shouldReceive('keepGitignore')
            ->once()
            ->andReturnSelf();

        $directoryCleaner->shouldReceive('clean')
            ->once()
            ->with(config('geonames.directory'));
    }

    protected function fakeDownloadService(): void
    {
        $downloadService = $this->mock(DownloadService::class);

        $downloadService->shouldReceive('downloadCountryInfoFile')
            ->once()
            ->andReturn($this->fixture('countryInfo.txt'));

        $downloadService->shouldReceive('downloadSourceFiles')
            ->once()
            ->andReturn([$this->fixture('allCountries.txt')]);

        $downloadService->shouldReceive('downloaderAlternateNames')
            ->once()
            ->andReturn([$this->fixture('alternateNames.txt')]);
    }
}
