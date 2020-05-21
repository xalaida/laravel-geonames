<?php

namespace Nevadskiy\Geonames\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Nevadskiy\Geonames\Support\Uuid;

/**
 * @property string id
 * @property string name
 * @property string country_id
 * @property float offset_dmt
 * @property float offset_dst
 * @property float offset_raw
 * @property Carbon created_at
 * @property Carbon updated_at
 */
class Timezone extends Model
{
    use Uuid;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    public const TABLE = 'timezones';

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
