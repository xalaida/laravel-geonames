<?php

namespace Nevadskiy\Geonames\Seeders;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\LazyCollection;

// TODO: extract to SyncsModels trait.
// TODO: split into traits.
// TODO: add soft deletes to deleted methods.
// TODO: define different methods for loading resources for seed/update/delete/sync
// TODO: define different methods for mapping for seed/update/delete/sync
abstract class ModelSeeder implements Seeder
{
    use SeedsModelRecords;
    use SyncsModelRecords;
    use UpdatesModelRecords;
    use MapsRecords;

    /**
     * The column name of the synced date.
     *
     * @var string
     */
    protected const SYNCED_AT = 'synced_at';

    /**
     * The column name of the sync key.
     *
     * @var string
     */
    protected const SYNC_KEY = 'geoname_id';

    /**
     * Get a new model instance of the seeder.
     */
    abstract protected function newModel(): Model;

    /**
     * Get a query instance of the seeder's model.
     */
    protected function query(): Builder
    {
        return $this->newModel()->newQuery();
    }

    /**
     * Truncate a table of the model.
     */
    public function truncate(): void
    {
        $this->query()->truncate();
    }

    /**
     * Perform a daily update of the database.
     */
    public function update(): void
    {
        $this->dailyUpdate();
        $this->dailyDelete();
    }

    /**
     * Delete records from database using the dataset with daily deletes.
     */
    protected function dailyDelete(): void
    {
        $records = LazyCollection::make(function () {
            foreach ($this->getRecordsForDailyDelete() as $record) {
                yield $record;
            }
        });

        foreach ($records->chunk(1000) as $records) {
            // TODO: refactor using mapSyncKey or something like that...
            $this->query()
                ->whereIn(self::SYNC_KEY, $records->pluck('geonameid')->all())
                ->delete();
        }
    }

    /**
     * Get records for daily delete.
     */
    abstract protected function getRecordsForDailyDelete(): iterable;
}
