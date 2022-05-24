<?php

namespace Nevadskiy\Geonames\Seeders;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\LazyCollection;

// TODO: add soft deletes to deleted methods.
abstract class ModelSeeder implements Seeder
{
    use SeedsModelRecords;
    use SyncsModelRecords;
    use DailyUpdateModelRecords;
    use DailyDeleteModelRecords;
    use BuildsReport;

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
        $report = $this->dailyUpdate();
        $report->incrementDeleted($this->dailyDelete());

        // TODO: log report ($report->logUsing($this->logger))
    }

    /**
     * Map the given dataset to records for seeding.
     */
    protected function mapRecords(iterable $records): LazyCollection
    {
        return LazyCollection::make(function () use ($records) {
            foreach ($records as $record) {
                if ($this->filter($record)) {
                    yield $this->map($record);
                }
            }
        });
    }

    /**
     * Map the given dataset to keyed records.
     */
    protected function mapRecordKeys(iterable $records): LazyCollection
    {
        return LazyCollection::make(function () use ($records) {
            foreach ($records as $record) {
                yield $this->mapKey($record) => $record;
            }
        });
    }

    /**
     * Determine if the given record should be seeded.
     */
    protected function filter(array $record): bool
    {
        return true;
    }

    /**
     * Map the given record to the model attributes.
     */
    protected function map(array $record): array
    {
        return $this->newModel()
            ->forceFill($this->mapAttributes($record))
            ->getAttributes();
    }

    /**
     * Map fields to the model attributes.
     */
    abstract protected function mapAttributes(array $record): array;

    /**
     * Map the record key.
     */
    protected function mapKey(array $record): string
    {
        return $record['geonameid'];
    }

    /**
     * Load resources before mapping.
     */
    protected function loadResourcesForMapping(): void
    {
        //
    }

    /**
     * Unload resources after mapping.
     */
    protected function unloadResourcesForMapping(): void
    {
        //
    }

    /**
     * Execute a callback with loaded mapping resources.
     */
    protected function withLoadedResources(callable $callback): void
    {
        $this->loadResourcesForMapping();

        $callback();

        $this->unloadResourcesForMapping();
    }
}
