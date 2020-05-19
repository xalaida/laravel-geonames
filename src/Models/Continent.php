<?php

namespace Nevadskiy\Geonames\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Nevadskiy\Geonames\Support\Slug;
use Nevadskiy\Geonames\Support\Uuid;

/**
 * @property string id
 * @property string slug
 * @property string name
 * @property string code
 * @property float lat
 * @property float lng
 * @property int population
 * @property int dem
 * @property int geoname_id
 * @property Carbon created_at
 * @property Carbon updated_at
 */
class Continent extends Model
{
    use Uuid,
        Slug;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    public const TABLE = 'continents';

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
