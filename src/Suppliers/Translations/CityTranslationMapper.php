<?php

namespace Nevadskiy\Geonames\Suppliers\Translations;

use Illuminate\Support\Collection;
use Nevadskiy\Geonames\Geonames;
use Nevadskiy\Geonames\Models\City;

class CityTranslationMapper implements TranslationMapper
{
    /**
     * The geonames instance.
     *
     * @var Geonames
     */
    protected $geonames;

    /**
     * Make a new translation mapper instance.
     */
    public function __construct(Geonames $geonames)
    {
        $this->geonames = $geonames;
    }

    /**
     * {@inheritdoc}
     */
    public function forEach(Collection $translations, callable $callback): void
    {
        foreach ($this->filterCities($translations) as $city) {
            foreach ($this->filterCityTranslations($city, $translations) as $translation) {
                $callback($city, $translation);
            }
        }
    }

    /**
     * Filter available cities by the given collection of translations.
     *
     * @return City[]|Collection
     */
    protected function filterCities(Collection $translations): Collection
    {
        return $this->geonames->model('city')
            ->newQuery()
            ->whereIn('geoname_id', $translations->pluck('geonameid'))
            ->get();
    }

    /**
     * Filter translations that belong to the given city.
     */
    protected function filterCityTranslations(City $city, Collection $translations): Collection
    {
        return $translations->where('geonameid', $city->geoname_id);
    }
}
