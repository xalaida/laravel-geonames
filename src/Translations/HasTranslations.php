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
     * @inheritdoc
     */
    protected function getEntityTranslationInstance(): Translation
    {
        return $this->newRelatedInstance(Translation::class);
    }
}
