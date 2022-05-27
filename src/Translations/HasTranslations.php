<?php

namespace Nevadskiy\Geonames\Translations;

use Illuminate\Database\Eloquent\Model;
use Nevadskiy\Translatable\Strategies\AdditionalTableExtended\HasTranslations as BaseHasTranslations;

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
