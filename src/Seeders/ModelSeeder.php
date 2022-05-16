<?php

namespace Nevadskiy\Geonames\Seeders;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

abstract class ModelSeeder
{
    /**
     * The seeder model class.
     */
    protected static $model;

    /**
     * Use the given model class.
     */
    public static function useModel(string $model): void
    {
        static::$model = $model;
    }

    /**
     * Get the model class.
     */
    public static function getModel(): Model
    {
        // TODO: check if class exists and is a subclass of eloquent model

        return new static::$model;
    }

    public function truncate(): void
    {
        $this->query()->truncate();
    }

    /**
     * Get a query of the model.
     */
    protected function query(): Builder
    {
        return static::getModel()->newQuery();
    }

    /**
     * Map the given record to the model attributes.
     */
    protected function mapRecord(array $record): array
    {
        return static::getModel()
            ->forceFill($this->mapFields($record))
            ->getAttributes();
    }

    /**
     * Map fields of the given record to the continent model attributes.
     */
    abstract protected function mapFields(array $record): array;
}
