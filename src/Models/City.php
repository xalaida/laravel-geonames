<?php

namespace Nevadskiy\Geonames\Models;

use Carbon\CarbonTimeZone;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Query\Expression;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Nevadskiy\Geonames\Support\Eloquent\Model;
use Nevadskiy\Geonames\ValueObjects\Location;
use Nevadskiy\Translatable\HasTranslations;

/**
 * @property string id
 * @property string name
 * @property string country_id
 * @property string division_id
 * @property float latitude
 * @property float longitude
 * @property string|null timezone_id
 * @property int|null population
 * @property int|null elevation
 * @property int|null dem
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

    /**
     * Order cities by feature code.
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeOrderByFeature(Builder $query): Builder
    {
        foreach (['PPLC', 'PPLA', 'PPLA2', 'PPLA3'] as $feature) {
            $query->orderByDesc(new Expression("feature_code = '{$feature}'"));
        }

        return $query;
    }
}
