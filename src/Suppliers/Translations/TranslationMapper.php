<?php

namespace Nevadskiy\Geonames\Suppliers\Translations;

use Illuminate\Support\Collection;

interface TranslationMapper
{
    /**
     * Apply the given callback for each mapped translations.
     */
    public function forEach(Collection $translations, callable $callback): void;
}
