<?php

namespace Nevadskiy\Geonames\Seeders;

use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Builder;

/**
 * @mixin ModelSeeder
 */
trait BuildsReport
{
    protected function withReport(callable $callback): Report
    {
        $report = new Report();

        $count = $this->query()->count();
        $syncedAt = $this->getPreviousSyncDate();

        $callback();

        $report->incrementCreated($this->query()->count() - $count);
        $report->incrementUpdated($this->getUpdateRecordsCountFrom($syncedAt));
        $report->incrementDeleted($this->deleteUnsyncedModels());

        return $report;
    }

    protected function getPreviousSyncDate(): ?DateTimeInterface
    {
        $syncedAt = $this->query()->max(self::SYNCED_AT);

        if (! $syncedAt) {
            return null;
        }

        return Carbon::parse($syncedAt);
    }

    protected function getUpdateRecordsCountFrom(?DateTimeInterface $date): int
    {
        return $this->query()
            ->when($date, function (Builder $query) use ($date) {
                $query->whereDate(self::SYNCED_AT, '>', $date);
            })
            ->count();
    }
}
