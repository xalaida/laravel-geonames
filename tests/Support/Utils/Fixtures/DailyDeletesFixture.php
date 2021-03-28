<?php

namespace Nevadskiy\Geonames\Tests\Support\Utils\Fixtures;

use Illuminate\Foundation\Testing\WithFaker;
use Nevadskiy\Geonames\Tests\Support\Utils\FixtureFileBuilder;

class DailyDeletesFixture
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
     *
     * @param array $data
     * @return string
     */
    public function create(array $data, string $filename = 'daily-deletes.txt'): string
    {
        return $this->builder->build($filename, $this->mergeData($data));
    }

    /**
     * Merge data with default attributes.
     *
     * @param array $data
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
     *
     * @return array
     */
    protected function defaults(): array
    {
        return [
            'geonameid' => $this->faker->unique()->randomNumber(6),
            'name' => $this->faker->word,
            'comment' => 'No longer exists',
        ];
    }
}
