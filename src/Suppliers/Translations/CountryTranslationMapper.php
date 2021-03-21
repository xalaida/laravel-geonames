<?php

namespace Nevadskiy\Geonames\Suppliers\Translations;

use Illuminate\Support\Collection;
use Nevadskiy\Geonames\Geonames;
use Nevadskiy\Geonames\Models\Country;

class CountryTranslationMapper implements TranslationMapper
{
    /**
     * The geonames instance.
     *
     * @var Geonames
     */
    protected $geonames;

    /**
     * The countries collection.
     *
     * @var Collection
     */
    protected $countries;

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
        $this->init();

        foreach ($this->filterCountries($translations) as $country) {
            foreach ($this->filterCountryTranslations($country, $translations) as $translation) {
                $callback($country, $translation);
            }
        }
    }

    /*
     * Init the mapper.
     */
    protected function init(): void
    {
        $this->countries = $this->countries ?: $this->getCountries();
    }

    /**
     * Get all countries.
     */
    protected function getCountries(): Collection
    {
        return $this->geonames->model('country')
            ->newQuery()
            ->get();
    }

    /**
     * Filter available countries for the given collection of translations.
     *
     * @return Country[]|Collection
     */
    protected function filterCountries(Collection $translations): Collection
    {
        return $this->countries->whereIn('geoname_id', $translations->pluck('geonameid'));
    }

    /**
     * Filter translations that belong to the given country.
     */
    protected function filterCountryTranslations(Country $country, Collection $translations): Collection
    {
        return $translations->where('geonameid', $country->geoname_id);
    }
}
