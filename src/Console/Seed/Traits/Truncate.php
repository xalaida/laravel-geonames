<?php

namespace Nevadskiy\Geonames\Console\Seed\Traits;

use Illuminate\Console\Command;
use Illuminate\Console\ConfirmableTrait;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * @mixin Command
 */
trait Truncate
{
    use ConfirmableTrait;

    /**
     * Truncate a table if the option is specified.
     */
    protected function truncateAttempt(): void
    {
        if (! $this->confirmToProceed()) {
            return;
        }

        if (! $this->option('truncate')) {
            return;
        }

        $this->performTruncate();
    }

    /**
     * Truncate a table.
     */
    private function performTruncate(): void
    {
        DB::table($this->getTableToTruncate())->truncate();
        $this->info("Table {$this->getTableToTruncate()} has been truncated.");
    }

    /**
     * Get a table name to be truncated.
     */
    protected function getTableToTruncate(): string
    {
        throw new RuntimeException("Specify a table name that can be truncated.");
    }
}
