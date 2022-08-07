<?php

namespace Nevadskiy\Geonames\Tests\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Nevadskiy\Geonames\Translations\HasTranslations;

/**
 * @property int id
 * @property string code
 * @property string name
 * @property float latitude
 * @property float longitude
 * @property string timezone_id
 * @property int population
 * @property int|null dem
 * @property string feature_code
 * @property int geoname_id
 * @property Carbon created_at
 * @property Carbon updated_at
 */
class Continent extends Model
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
     * Get a relationship with countries.
     */
    public function countries(): HasMany
    {
        return $this->hasMany(Country::class);
    }
}
