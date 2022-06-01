<?php

namespace Nevadskiy\Geonames\Translations;

use Illuminate\Database\Eloquent\Model;
use Nevadskiy\Translatable\Strategies\ExtraTableExtended\HasTranslations as BaseHasTranslations;

/**
 * @mixin Model
 */
trait HasTranslations
{
    use BaseHasTranslations;

    /**
     * Get the translation model class.
     */
    protected function getTranslationModelClass(): string
    {
        return Translation::class;
    }
}
