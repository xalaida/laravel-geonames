<?php

namespace Nevadskiy\Geonames\Seeders;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Nevadskiy\Geonames\Reader\DeletesReader;
use Nevadskiy\Geonames\Reader\GeonamesReader;
use RuntimeException;

/**
 * @TODO: add possibility to use custom delete scopes (by overriding default seeders).
 */
abstract class ModelSeeder extends BaseSeeder
{
    /**
     * @inheritdoc
     */
    public function getSyncKeyName(): string
    {
        return 'geoname_id';
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
     * {@inheritdoc}
     */
    protected function getRecords(): iterable
    {
        return $this->source->getRecords();
    }

    /**
     * {@inheritdoc}
     */
    protected function getDailyModificationRecords(): iterable
    {
        return $this->source->getDailyModificationRecords();
    }

    /**
     * {@inheritdoc}
     */
    protected function getDailyDeleteRecords(): iterable
    {
        return $this->source->getDailyDeleteRecords();
    }

    /**
     * Get a sync key by the given record.
     */
    protected function getSyncKeyByRecord(array $record): int
    {
        return $record['geonameid'];
    }
}
