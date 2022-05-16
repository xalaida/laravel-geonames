<?php

namespace Nevadskiy\Geonames\Seeders;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

abstract class ModelSeeder
{
    /**
     * Get a model of the seeder.
     */
    abstract public static function getModel(): Model;

    /**
     * Get a query of the model.
     */
    protected function query(): Builder
    {
        return static::getModel()->newQuery();
    }

    /**
     * Truncate a table of the model.
     */
    public function truncate(): void
    {
        $this->query()->truncate();
    }

    /**
     * Map the given record to the database fields.
     */
    protected function map(array $record): array
    {
        return static::getModel()
            ->forceFill($this->mapAttributes($record))
            ->getAttributes();
    }

    /**
     * Map the given record to the model attributes.
     */
    abstract protected function mapAttributes(array $record): array;
}
