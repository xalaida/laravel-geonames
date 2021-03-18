<?php

namespace Nevadskiy\Geonames\Suppliers\Translations;

use Illuminate\Support\Collection;

class CompositeTranslationMapper implements TranslationMapper
{
    /**
     * The array of available translation mappers.
     *
     * @var array<TranslationMapper>
     */
    private $mappers;

    /**
     * Make a new composite mapper instance.
     */
    public function __construct(array $mappers)
    {
        $this->mappers = $mappers;
    }

    /**
     * {@inheritdoc}
     */
    public function forEach(Collection $translations, callable $callback): void
    {
        foreach ($this->mappers as $mapper) {
            $mapper->forEach($translations, $callback);
        }
    }
}
