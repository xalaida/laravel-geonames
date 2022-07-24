<?php

namespace Nevadskiy\Geonames\Tests\Models;

use Carbon\CarbonTimeZone;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Nevadskiy\Geonames\Translations\HasTranslations;

/**
 * @property int id
 * @property string name
 * @property int country_id
 * @property float latitude
 * @property float longitude
 * @property string|null timezone_id
 * @property int|null population
 * @property int|null elevation
 * @property int|null dem
 * @property string code
 * @property string feature_code
 * @property int geoname_id
 * @property Carbon created_at
 * @property Carbon updated_at
 */
class Division extends Model
{
    use HasTranslations;

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'latitude' => 'float',
        'longitude' => 'float',
    ];

    /**
     * Attributes that are translatable.
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
     * Get a relationship with a country.
     */
    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    /**
     * Get a relationship with cities.
     */
    public function cities(): HasMany
    {
        return $this->hasMany(City::class);
    }
}