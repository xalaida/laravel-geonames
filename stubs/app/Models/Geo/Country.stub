<?php

namespace App\Models\Geo;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Nevadskiy\Geonames\Translations\HasTranslations;

/**
 * @property int id
 * @property string code
 * @property string iso
 * @property string iso_numeric
 * @property string name
 * @property string name_official
 * @property float latitude
 * @property float longitude
 * @property string|null timezone_id
 * @property int continent_id
 * @property string|null capital
 * @property string|null currency_code
 * @property string|null currency_name
 * @property string|null tld
 * @property string|null phone_code
 * @property string|null postal_code_format
 * @property string|null postal_code_regex
 * @property string|null languages
 * @property string|null neighbours
 * @property float|null area
 * @property string|null fips
 * @property int|null population
 * @property int|null dem
 * @property string|null feature_code
 * @property int|null geoname_id
 * @property Carbon|null created_at
 * @property Carbon|null updated_at
 */
class Country extends Model
{
    use HasTranslations;

    /**
     * Attributes that are translatable.
     *
     * @var array
     */
    protected $translatable = [
        'name',
    ];

    /**
     * Get a relationship with a continent.
     */
    public function continent(): BelongsTo
    {
        return $this->belongsTo(Continent::class);
    }

    /**
     * Get a relationship with divisions.
     */
    public function divisions(): HasMany
    {
        return $this->hasMany(Division::class);
    }

    /**
     * Get a relationship with cities.
     */
    public function cities(): HasMany
    {
        return $this->hasMany(City::class);
    }
}
