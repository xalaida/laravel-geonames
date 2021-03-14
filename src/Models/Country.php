<?php

namespace Nevadskiy\Geonames\Models;

use Carbon\CarbonTimeZone;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Nevadskiy\Geonames\ValueObjects\Location;
use Nevadskiy\Geonames\Support\Eloquent\Model;
use Nevadskiy\Translatable\HasTranslations;

/**
 * @property string id
 * @property string code
 * @property string iso
 * @property string iso_numeric
 * @property string name
 * @property string name_official
 * @property float latitude
 * @property float longitude
 * @property string|null timezone_id
 * @property string continent_id
 * @property string|null capital
 * @property string|null currency_code
 * @property string|null currency_name
 * @property string|null tld
 * @property string|null phone_code
 * @property string|null postal_code_format
 * @property string|null postal_code_regex
 * @property string|null languages
 * @property string|null neighbours
 * @property float area
 * @property string|null fips
 * @property int population
 * @property int dem
 * @property string feature_code
 * @property int geoname_id
 * @property Carbon modified_at
 * @property Carbon created_at
 * @property Carbon updated_at
 */
class Country extends Model
{
    use HasTranslations;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    public const TABLE = 'countries';

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
     * Get the continent relation.
     *
     * @return BelongsTo
     */
    public function continent(): BelongsTo
    {
        return $this->belongsTo(Continent::class, 'continent_id', 'id', 'continent');
    }

    /**
     * Get the divisions relation.
     *
     * @return HasMany
     */
    public function divisions(): HasMany
    {
        return $this->hasMany(Division::class, 'country_id', 'id');
    }
}
