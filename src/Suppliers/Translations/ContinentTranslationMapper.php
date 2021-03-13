<?php

namespace Nevadskiy\Geonames\Suppliers\Translations;

use Illuminate\Support\Collection;
use Nevadskiy\Geonames\Models\Continent;

class ContinentTranslationMapper implements TranslationMapper
{
    /**
     * The continents collection.
     *
     * @var Collection
     */
    protected $continents;

    /**
     * @inheritDoc
     */
    public function forEach(Collection $translations, callable $callback): void
    {
        $this->init();

        foreach ($this->filterContinents($translations) as $continent) {
            foreach ($this->filterContinentTranslations($continent, $translations) as $translation) {
                $callback($continent, $translation);
            }
        }
    }

    /*
     * Init the mapper.
     */
    protected function init(): void
    {
        $this->continents = $this->continents ?: $this->getContinents();
    }

    /**
     * Get all continents.
     */
    protected function getContinents(): Collection
    {
        return Continent::query()->get();
    }

    /**
     * Filter available continents for the given collection of translations.
     *
     * @return Continent[]|Collection
     */
    protected function filterContinents(Collection $translations): Collection
    {
        return $this->continents->whereIn('geoname_id', $translations->pluck('geonameid'));
    }

    /**
     * Filter translations that belong to the given continent.
     */
    protected function filterContinentTranslations(Continent $continent, Collection $translations): Collection
    {
        return $translations->where('geonameid', $continent->geoname_id);
    }
}
