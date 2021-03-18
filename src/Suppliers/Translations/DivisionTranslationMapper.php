<?php

namespace Nevadskiy\Geonames\Suppliers\Translations;

use Illuminate\Support\Collection;
use Nevadskiy\Geonames\Models\Division;

class DivisionTranslationMapper implements TranslationMapper
{
    /**
     * {@inheritdoc}
     */
    public function forEach(Collection $translations, callable $callback): void
    {
        foreach ($this->filterDivisions($translations) as $division) {
            foreach ($this->filterDivisionTranslations($division, $translations) as $translation) {
                $callback($division, $translation);
            }
        }
    }

    /**
     * Filter available divisions by the given collection of translations.
     *
     * @return Division[]|Collection
     */
    protected function filterDivisions(Collection $translations): Collection
    {
        return Division::query()->whereIn('geoname_id', $translations->pluck('geonameid'))->get();
    }

    /**
     * Filter translations that belong to the given division.
     */
    protected function filterDivisionTranslations(Division $division, Collection $translations): Collection
    {
        return $translations->where('geonameid', $division->geoname_id);
    }
}
