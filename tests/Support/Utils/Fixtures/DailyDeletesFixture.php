<?php

namespace Nevadskiy\Geonames\Tests\Support\Utils\Fixtures;

class DailyDeletesFixture extends Fixture
{
    /**
     * Get default attributes.
     */
    protected function defaults(): array
    {
        return [
            'geonameid' => $this->faker->unique()->numerify('######'),
            'name' => $this->faker->word,
            'comment' => 'No longer exists',
        ];
    }

    /**
     * Get the default filename.
     */
    protected function filename(): string
    {
        return 'daily-deletes.txt';
    }
}
