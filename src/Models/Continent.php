<?php

namespace Nevadskiy\Geonames\Models;

use Carbon\CarbonTimeZone;
use Illuminate\Support\Carbon;
use Nevadskiy\Geonames\ValueObjects\Location;
use Nevadskiy\Geonames\Support\Eloquent\Model;
use Nevadskiy\Translatable\HasTranslations;

/**
 * @property string id
 * @property string slug
 * @property string code
 * @property string name
 * @property float latitude
 * @property float longitude
 * @property string timezone_id
 * @property int population
 * @property integer|null dem
 * @property string feature_code
 * @property int geoname_id
 * @property Carbon modified_at
 * @property Carbon created_at
 * @property Carbon updated_at
 */
class Continent extends Model
{
    use HasTranslations;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    public const TABLE = 'continents';

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'latitude' => 'float',
        'longitude' => 'float',
        'modified_at' => 'date',
    ];

    /**
     * The attributes that can be translatable.
     *
     * @var array
     */
    protected $translatable = [
        'name',
    ];

    /**
     * Get the location instance.
     */
    public function getLocation(): Location
    {
        return new Location($this->latitude, $this->longitude);
    }

    /**
     * Get the timezone instance.
     */
    public function getTimezone(): CarbonTimeZone
    {
        return new CarbonTimeZone($this->timezone_id);
    }
}
