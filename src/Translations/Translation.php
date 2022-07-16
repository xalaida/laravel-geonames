<?php

namespace Nevadskiy\Geonames\Translations;

use Illuminate\Database\Eloquent\Builder;
use Nevadskiy\Translatable\Strategies\ExtraTable\Models\Translation as BaseTranslations;

class Translation extends BaseTranslations
{
    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'is_preferred' => 'boolean',
        'is_short' => 'boolean',
        'is_colloquial' => 'boolean',
        'is_historic' => 'boolean',
    ];

    /**
     * Perform any actions required after the model boots.
     */
    protected static function booted(): void
    {
        static::addGlobalScope('sorting', function (Builder $query) {
            $query->orderBy('is_preferred');
            $query->orderByDesc('is_historic');
        });
    }
}
