<?php

namespace Nevadskiy\Geonames\Console\Traits;

use Illuminate\Console\Command;
use Nevadskiy\Geonames\Geonames;
use Nevadskiy\Geonames\Support\Cleaner\DirectoryCleaner;

/**
 * @mixin Command
 * @property-read Geonames geonames
 * @property-read DirectoryCleaner directoryCleaner
 */
trait CleanFolder
{
    /**
     * Clean the resource downloads folder.
     */
    protected function cleanFolder(): void
    {
        if ($this->option('keep-files')) {
            return;
        }

        $this->directoryCleaner->keepGitignore()
            ->clean($this->geonames->directory());

        $this->info('Downloads folder has been cleaned.');
    }
}
