<?php

namespace Nevadskiy\Geonames\Tests\Integration;

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Schema;
use Nevadskiy\Geonames\Tests\TestCase;
use Nevadskiy\Translatable\Models\Translation;

class DisableTranslationsTest extends TestCase
{
    /**
     * Define environment setup.
     *
     * @param Application $app
     */
    protected function getEnvironmentSetUp($app): void
    {
        parent::getEnvironmentSetUp($app);

        $app['config']->set('geonames.translations', false);
    }

    /** @test */
    public function it_can_disable_translations_table(): void
    {
        $this->migrate();

        self::assertFalse(
            Schema::hasTable((new Translation())->getTable())
        );
    }
}
