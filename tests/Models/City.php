<?php

namespace Nevadskiy\Geonames\Tests\Models;

use Carbon\CarbonTimeZone;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Query\Expression;
use Illuminate\Support\Carbon;
use Nevadskiy\Geonames\Definitions\FeatureCode;
use Nevadskiy\Geonames\Translations\HasTranslations;

/**
 * @property int id
 * @property string name
 * @property int country_id
 * @property int division_id
 * @property float latitude
 * @property float longitude
 * @property string|null timezone_id
 * @property int|null population
 * @property int|null elevation
 * @property int|null dem
 * @property string feature_code
 * @property int geoname_id
 * @property Carbon created_at
 * @property Carbon updated_at
 */
class City extends Model
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
     * Get a relationship with a division.
     */
    public function division(): BelongsTo
    {
        return $this->belongsTo(Division::class);
    }

    /**
     * Order cities by a feature code.
     */
    public function scopeOrderByFeature(Builder $query): Builder
    {
        foreach ([FeatureCode::PPLC, FeatureCode::PPLA, FeatureCode::PPLA2, FeatureCode::PPLA3] as $feature) {
            // TODO: update with bindings
            $query->orderByDesc(new Expression("feature_code = '{$feature}'"));
            // $query->orderByRaw("feature_code", [$feature]);
        }

        return $query;
    }
}
