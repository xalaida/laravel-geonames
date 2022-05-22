<?php

namespace Nevadskiy\Geonames\Seeders;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

// TODO: add soft deletes to deleted methods.
// TODO: define different methods for loading resources for seed/update/delete/sync
// TODO: define different methods for mapping for seed/update/delete/sync
abstract class ModelSeeder implements Seeder
{
    use SeedsModelRecords;
    use SyncsModelRecords;
    use DailyUpdate;
    use DailyDelete;
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
        // $this->dailyDelete();
    }

    /**
     * Map the record key.
     */
    protected function mapKey(array $record): string
    {
        return $record['geonameid'];
    }
}
