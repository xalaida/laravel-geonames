<?php

namespace Nevadskiy\Geonames\Translations;

use Illuminate\Database\Eloquent\Model;
use Nevadskiy\Translatable\Strategies\AdditionalTableExtended\HasTranslations as BaseHasTranslations;

/**
 * @mixin Model
 * @todo delete this file and add getTranslationModelClass() method to each model.
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
