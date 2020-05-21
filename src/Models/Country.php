<?php

namespace Nevadskiy\Geonames\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Nevadskiy\Geonames\Support\Slug;
use Nevadskiy\Geonames\Support\Uuid;

/**
 * @property string id
 * @property string slug
 * @property string iso
 * @property string iso3
 * @property string iso_numeric
 * @property string fips
 * @property string name
 * @property string name_official
 * @property string capital
 * @property int area
 * @property int population
 * @property string continent_id
 * @property string tld
 * @property string currency_code
 * @property string currency_name
 * @property string phone_code
 * @property string postal_code_format
 * @property string postal_code_regex
 * @property string languages
 * @property string neighbours
 * @property float latitude
 * @property float longitude
 * @property int dem
 * @property string feature_code
 * @property int geoname_id
 * @property Carbon created_at
 * @property Carbon updated_at
 */
class Country extends Model
{
    use Uuid,
        Slug;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    public const TABLE = 'countries';

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

    /**
     * Get the slug source key name.
     *
     * @return string
     */
    public function getSlugSourceKeyName(): string
    {
        return 'iso';
    }
}
