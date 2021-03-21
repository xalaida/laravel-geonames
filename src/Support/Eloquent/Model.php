<?php

namespace Nevadskiy\Geonames\Support\Eloquent;

use Illuminate\Database\Eloquent\Model as EloquentModel;
use Nevadskiy\Uuid\Uuid;

/**
 * @property string id
 */
abstract class Model extends EloquentModel
{
    use Uuid;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array|bool
     */
    protected $guarded = [];

    /**
     * Get the table associated with the model.
     */
    public function getTable(): string
    {
        if (defined('static::TABLE')) {
            return static::TABLE;
        }

        return parent::getTable();
    }
}
