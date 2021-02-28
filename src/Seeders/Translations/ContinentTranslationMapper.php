<?php

namespace Nevadskiy\Geonames\Seeders\Translations;

use Illuminate\Support\Collection;
use Nevadskiy\Geonames\Models\Continent;

class ContinentTranslationMapper
{
    /**
     * The continents collection.
     *
     * @var Collection
     */
    protected $continents;

    /**
     * ContinentTranslationMapper constructor.
     */
    public function __construct()
    {
        $this->continents = $this->getContinents();
    }

    /**
     * Apply the given callback for each continent translation map.
     */
    public function forEach(Collection $translations, callable $callback): void
    {
        foreach ($this->filterContinents($translations) as $continent) {
            foreach ($this->filterContinentTranslations($continent, $translations) as $translation) {
                $callback($continent, $translation);
            }
        }
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
