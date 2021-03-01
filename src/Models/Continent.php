<?php

namespace Nevadskiy\Geonames\Models;

use Carbon\CarbonTimeZone;
use Illuminate\Support\Carbon;
use Nevadskiy\Geonames\Services\ContinentCodeGenerator;
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
 * @property int dem
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
     * @inheritDoc
     */
    protected static function booted(): void
    {
        self::creating(static function (self $continent) {
            $continent->code = (new ContinentCodeGenerator())->generate($continent);
        });
    }

    /**
     * Get the timezone instance.
     */
    public function getTimezone(): CarbonTimeZone
    {
        return new CarbonTimeZone($this->timezone_id);
    }

    /**
     * Get the location instance.
     */
    public function getLocation(): Location
    {
        return new Location($this->latitude, $this->longitude);
    }
}
