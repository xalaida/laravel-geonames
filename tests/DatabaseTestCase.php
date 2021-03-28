<?php

namespace Nevadskiy\Geonames\Tests;

class DatabaseTestCase extends TestCase
{
    /**
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->migrate();
    }
}
