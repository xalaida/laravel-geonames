<?php

namespace Nevadskiy\Geonames\Seeders;

trait LoadsMappingResources
{
    /**
     * Load resources before record attributes mapping.
     */
    protected function loadResourcesBeforeMapping(): void
    {
        //
    }

    /**
     * Unload resources after record attributes mapping.
     */
    protected function unloadResourcesAfterMapping(): void
    {
        //
    }

    /**
     * Execute a callback with loaded mapping resources.
     */
    protected function withLoadedResources(callable $callback): void
    {
        $this->loadResourcesBeforeMapping();

        $callback();

        $this->unloadResourcesAfterMapping();
    }
}
