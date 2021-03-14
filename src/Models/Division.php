<?php

namespace Nevadskiy\Geonames\Models;

use Carbon\CarbonTimeZone;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Nevadskiy\Geonames\Support\Eloquent\Model;
use Nevadskiy\Geonames\ValueObjects\Location;
use Nevadskiy\Translatable\HasTranslations;

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
    use HasTranslations;

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
    public function getTimezone(): ?CarbonTimeZone
    {
        if (! $this->timezone_id) {
            return null;
        }

        return new CarbonTimeZone($this->timezone_id);
    }

    /**
     * Get the country relation.
     *
     * @return BelongsTo
     */
    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'country_id', 'id', 'country');
    }

    /**
     * Get the cities relation.
     *
     * @return HasMany
     */
    public function cities(): HasMany
    {
        return $this->hasMany(City::class, 'division_id', 'id');
    }
}
