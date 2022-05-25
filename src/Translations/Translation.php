<?php

namespace Nevadskiy\Geonames\Translations;

use Illuminate\Database\Eloquent\Model;

class Translation extends Model
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

    // TODO: probably extend AdditionalTable strategy model.
    // TODO: add some scopes for translation priority.
    // TODO: make model use this trait
}
