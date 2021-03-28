<?php

namespace Nevadskiy\Geonames\Tests\Support\Utils\Fixtures;

use Illuminate\Foundation\Testing\WithFaker;
use Nevadskiy\Geonames\Tests\Support\Utils\FixtureFileBuilder;

abstract class Fixture
{
    use WithFaker;

    /**
     * @var FixtureFileBuilder
     */
    private $builder;

    /**
     * DailyDeletesFixture constructor.
     */
    public function __construct(FixtureFileBuilder $builder)
    {
        $this->builder = $builder;
        $this->setUpFaker();
    }

    /**
     * Create fixture file from the given data.
     */
    public function create(array $data, string $filename = null): string
    {
        return $this->builder->build($filename ?: $this->filename(), $this->mergeData($data));
    }

    /**
     * Merge data with default attributes.
     *
     * @return array|array[]
     */
    protected function mergeData(array $data): array
    {
        return array_map(function ($row) {
            return array_merge($this->defaults(), $row);
        }, $data);
    }

    /**
     * Get default attributes.
     */
    protected function defaults(): array
    {
        return [];
    }

    /**
     * Get the default filename.
     */
    protected function filename(): string
    {
        return 'fixture.txt';
    }
}
