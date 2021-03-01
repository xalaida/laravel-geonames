<?php

namespace Nevadskiy\Geonames\Models;

use Carbon\CarbonTimeZone;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Nevadskiy\Geonames\ValueObjects\Location;
use Nevadskiy\Geonames\Support\Eloquent\Model;
use Nevadskiy\Translatable\HasTranslations;

/**
 * @property string id
 * @property string name
 * @property string country_id
 * @property string division_id
 * @property float latitude
 * @property float longitude
 * @property string|null timezone_id
 * @property integer|null population
 * @property integer|null elevation
 * @property integer|null dem
 * @property string feature_code
 * @property int geoname_id
 * @property Carbon modified_at
 * @property Carbon created_at
 * @property Carbon updated_at
 */
class City extends Model
{
    use HasTranslations;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    public const TABLE = 'cities';

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
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
     * Get the location instance.
     */
    public function getLocation(): Location
    {
        return new Location($this->latitude, $this->longitude);
    }

    /**
     * Get a country of the city.
     */
    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'country_id');
    }

    /**
     * Get a division of the city.
     */
    public function division(): BelongsTo
    {
        return $this->belongsTo(Division::class, 'division_id');
    }
}
