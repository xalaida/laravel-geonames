<?php

namespace Nevadskiy\Geonames\Seeders;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Nevadskiy\Geonames\Reader\DeletesReader;
use Nevadskiy\Geonames\Reader\GeonamesReader;
use Nevadskiy\Geonames\Services\DownloadService;
use RuntimeException;

/**
 * @TODO: add soft deletes to deleted methods.
 * @TODO: add possibility to use custom delete scopes (by overriding default seeders).
 */
abstract class NextModelSeeder extends BaseSeeder
{
    /**
     * The column name of the sync key.
     *
     * @var string
     */
    protected const SYNC_KEY = 'geoname_id';

    /**
     * @inheritdoc
     */
    public function getSyncKey(): string
    {
        return static::SYNC_KEY;
    }

    /**
     * Get the model name of the seeder.
     */
    abstract public static function model(): string;

    /**
     * Get a new model instance of the seeder.
     */
    public static function newModel(): Model
    {
        $model = static::model();

        if (! is_a($model, Model::class, true)) {
            throw new RuntimeException(sprintf('The seeder model "%s" is not an Eloquent model.', $model));
        }

        return new $model;
    }

    /**
     * Get a query instance of the seeder's model.
     */
    protected function query(): Builder
    {
        return static::newModel()->newQuery();
    }

    /**
     * Map the given record to the model attributes.
     */
    protected function map(array $record): array
    {
        return static::newModel()
            ->forceFill($this->mapAttributes($record))
            ->getAttributes();
    }

    /**
     * {@inheritdoc}
     */
    protected function getRecords(): iterable
    {
        return (new GeonamesReader($this->reader))->getRecords(
            (new DownloadService($this->downloader))->downloadAllCountries()
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function getDailyModificationRecords(): iterable
    {
        return (new GeonamesReader($this->reader))->getRecords(
            (new DownloadService($this->downloader))->downloadDailyModifications()
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function getDailyDeleteRecords(): iterable
    {
        return (new DeletesReader($this->reader))->getRecords(
            (new DownloadService($this->downloader))->downloadDailyDeletes()
        );
    }
}
