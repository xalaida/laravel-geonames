<?php

namespace Nevadskiy\Geonames\Seeders;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

trait HasModel
{
    /**
     * The continent model class.
     */
    protected static $model;

    /**
     * Use the given model class.
     */
    public static function useModel(string $model): void
    {
        self::$model = $model;
    }

    /**
     * Get the model class.
     */
    public static function getModel(): Model
    {
        // TODO: check if class exists and is a subclass of eloquent model

        return new self::$model;
    }

    /**
     * Get a query of the model.
     */
    protected function query(): Builder
    {
        return static::getModel()->newQuery();
    }
}
