<?php

namespace Nevadskiy\Geonames\Models;

use Illuminate\Support\Carbon;
use Nevadskiy\Geonames\Support\Eloquent\Model;

/**
 * @property string id
 * @property string name
 * @property string country_id
 * @property float latitude
 * @property float longitude
 * @property string|null timezone_id
 * @property integer|null population
 * @property integer|null elevation
 * @property integer|null dem
 * @property string code
 * @property string feature_code
 * @property int geoname_id
 * @property Carbon modified_at
 * @property Carbon created_at
 * @property Carbon updated_at
 */
class Division extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    public const TABLE = 'divisions';

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'modified_at' => 'date',
    ];
}
