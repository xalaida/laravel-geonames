<?php

namespace Nevadskiy\Geonames\Models;

use Illuminate\Database\Eloquent\Model;
use Nevadskiy\Geonames\Support\Uuid;

/**
 * @property string id
 */
class City extends Model
{
    use Uuid;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    public const TABLE = 'cities';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = self::TABLE;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];
}
