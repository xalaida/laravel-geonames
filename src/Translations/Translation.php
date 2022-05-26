<?php

namespace Nevadskiy\Geonames\Translations;

use Nevadskiy\Translatable\Strategies\AdditionalTable\Models\Translation as BaseTranslations;

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
        'is_synced' => 'boolean',
    ];

    // TODO: add some scopes for translation priority.
}
