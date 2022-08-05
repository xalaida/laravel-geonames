<?php

namespace Nevadskiy\Geonames\Tests\Support\Fake;

use Nevadskiy\Geonames\Geonames;

class FixtureGenerator
{
    /**
     * The geonames instance.
     *
     * @var Geonames
     */
    private $geonames;

    /**
     * FixtureGenerator constructor.
     */
    public function __construct(Geonames $geonames)
    {
        $this->geonames = $geonames;
    }

    public function generate(array $data)
    {
        $this->createFile('daily-modifications.txt', $this->prepareTableContent($data));

//        $downloadService = $this->mock(DownloadService::class);
//
//        $downloadService->shouldReceive('downloadCountryInfo')
//            ->withNoArgs()
//            ->andReturn($this->fixture('countryInfo.txt'));
//
//        $downloadService->shouldReceive('downloadDailyModifications')
//            ->withNoArgs()
//            ->andReturn($storage->path('daily-modifications.txt'));
    }

    protected function prepareTableContent(array $table, string $rowSeparator = "\n", string $colSeparator = "\t"): string
    {
        // Prepare headers
        array_unshift($table, array_keys(reset($table)));

        // Build content
        return implode($rowSeparator, array_map(static function ($row) use ($colSeparator) {
            return implode($colSeparator, $row);
        }, $table));
    }

    protected function createFile(string $filename, string $content): void
    {
        file_put_contents("{$this->geonames->directory()}/{$filename}", $content);
    }
}
