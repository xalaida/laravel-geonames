<?php

namespace Nevadskiy\Geonames\Tests\Support\Utils;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Foundation\Testing\Concerns\InteractsWithContainer;
use Nevadskiy\Geonames\GeonamesDownloader;
use Nevadskiy\Geonames\Tests\Support\Utils\Fixtures\CountryInfoFixture;
use Nevadskiy\Geonames\Tests\Support\Utils\Fixtures\DailyDeletesFixture;
use Nevadskiy\Geonames\Tests\Support\Utils\Fixtures\GeonamesFixture;

class FakeDownloadService
{
    use InteractsWithContainer;

    /**
     * The Illuminate application instance.
     *
     * @var Application
     */
    protected $app;

    /**
     * FakeDownloadService constructor.
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * Make a new fake download service instance.
     */
    public static function new(Application $app): self
    {
        return new static($app);
    }

    /**
     * Get defined paths.
     */
    protected $paths = [
        'downloadCountryInfo' => null,
        'downloadDailyModifications' => null,
        'downloadDailyDeletes' => null,
        'downloadDailyAlternateNamesModifications' => null,
        'downloadDailyAlternateNamesDeletes' => null,
    ];

    /**
     * Fake country info file.
     *
     * @param string|array $fixture
     * @return $this
     */
    public function countryInfo($fixture): self
    {
        $this->paths['downloadCountryInfo'] = is_array($fixture)
            ? $this->app[CountryInfoFixture::class]->create($fixture)
            : $fixture;

        return $this;
    }

    /**
     * Fake daily modifications file.
     *
     * @param string|array $fixture
     * @return $this
     */
    public function dailyModifications($fixture): self
    {
        $this->paths['downloadDailyModifications'] = is_array($fixture)
            ? $this->app[GeonamesFixture::class]->create($fixture)
            : $fixture;

        return $this;
    }

    /**
     * Fake daily deletes file.
     *
     * @param string|array $fixture
     * @return $this
     */
    public function dailyDeletes($fixture): self
    {
        $this->paths['downloadDailyDeletes'] = is_array($fixture)
            ? $this->app[DailyDeletesFixture::class]->create($fixture)
            : $fixture;

        return $this;
    }

    /**
     * Fake daily alternate names modifications file.
     *
     * @return $this
     */
    public function dailyAlternateNamesModifications(string $path): self
    {
        $this->paths['downloadDailyAlternateNamesModifications'] = $path;

        return $this;
    }

    /**
     * Fake daily alternate names deletes file.
     *
     * @return $this
     */
    public function dailyAlternateNamesDeletes(string $path): self
    {
        $this->paths['downloadDailyAlternateNamesDeletes'] = $path;

        return $this;
    }

    /**
     * Swap the fake download service with original one.
     */
    public function swap(): void
    {
        $downloadService = $this->mock(GeonamesDownloader::class);

        foreach ($this->paths as $method => $path) {
            $downloadService->shouldReceive($method)
                ->withNoArgs()
                ->andReturn($path ?: self::fixture('empty.txt'));
        }
    }

    /**
     * Get the fixture path.
     */
    public static function fixture(string $path): string
    {
        return __DIR__."/../fixtures/{$path}";
    }
}
