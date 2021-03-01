<?php

namespace Nevadskiy\Geonames\Seeders\Translations;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Nevadskiy\Geonames\Models\Continent;
use Nevadskiy\Geonames\Models\Country;
use Nevadskiy\Geonames\Models\Division;
use Nevadskiy\Geonames\Models\City;
use Nevadskiy\Geonames\Support\Batch\Batch;
use Nevadskiy\Translatable\Models\Translation;

class TranslationDefaultSeeder implements TranslationSeeder
{
    /**
     * The languages array.
     *
     * @var array|string[]
     */
    protected $languages;

    /**
     * Indicates if nullable languages should be seeded.
     *
     * @var bool
     */
    private $nullableLanguage;

    /**
     * Continent translation mapper.
     *
     * @var ContinentTranslationMapper
     */
    protected $continentTranslationMapper;

    /**
     * Country translation mapper.
     *
     * @var CountryTranslationMapper
     */
    protected $countryTranslationMapper;

    /**
     * City translation mapper.
     *
     * @var CityTranslationMapper
     */
    protected $cityTranslationMapper;

    /**
     * Division translation mapper.
     *
     * @var DivisionTranslationMapper
     */
    protected $divisionTranslationMapper;

    /**
     * A batch of source translations.
     *
     * @var Batch
     */
    protected $sourceTranslations;

    /**
     * The batch of prepared translations.
     *
     * @var Batch
     */
    protected $preparedTranslations;

    /**
     * Make a new seeder instance.
     */
    public function __construct(
        ContinentTranslationMapper $continentTranslationMapper,
        CountryTranslationMapper $countryTranslationMapper,
        CityTranslationMapper $cityTranslationMapper,
        DivisionTranslationMapper $divisionTranslationMapper,
        array $languages,
        bool $nullableLanguage
    )
    {
        $this->continentTranslationMapper = $continentTranslationMapper;
        $this->countryTranslationMapper = $countryTranslationMapper;
        $this->cityTranslationMapper = $cityTranslationMapper;
        $this->divisionTranslationMapper = $divisionTranslationMapper;
        $this->languages = $languages;
        $this->nullableLanguage = $nullableLanguage;
        $this->sourceTranslations = $this->makeSourceTranslationsBatch();
        $this->preparedTranslations = $this->makePreparedTranslationsBatch();
    }

    /**
     * Make a batch instance for source translations.
     *
     * @return Batch
     */
    protected function makeSourceTranslationsBatch(): Batch
    {
        return new Batch(function (array $items) {
            $collection = collect($items);
            $this->translateContinents($collection);
            $this->translateCountries($collection);
            $this->translateDivisions($collection);
            $this->translateCities($collection);
        }, 1000);
    }

    /**
     * @inheritDoc
     */
    public function seed(array $translation, int $id): void
    {
        if ($this->shouldSeed($translation)) {
            $this->sourceTranslations->push($translation);
        }
    }

    /**
     * Determine whether the translation should be seeded.
     */
    protected function shouldSeed(array $translation): bool
    {
        return (is_null($translation['isolanguage']) && $this->nullableLanguage)
            || in_array($translation['isolanguage'], $this->languages, true);
    }

    /**
     * Make a batch instance for prepared translations.
     *
     * @return Batch
     */
    protected function makePreparedTranslationsBatch(): Batch
    {
        return new Batch(function (array $items) {
            DB::table('translations')->insert($items);
        }, 1000);
    }

    /**
     * Translate continents using the given collection of translations.
     */
    protected function translateContinents(Collection $translations): void
    {
        $this->continentTranslationMapper->forEach($translations, function ($continent, $translation) {
            $this->addTranslation($translation, $continent);
        });
    }

    /**
     * Translate countries using the given collection of translations.
     */
    protected function translateCountries(Collection $translations): void
    {
        $this->countryTranslationMapper->forEach($translations, function ($country, $translation) {
            $this->addTranslation($translation, $country);
        });
    }

    /**
     * Translate cities using the given collection of translations.
     */
    protected function translateCities(Collection $translations): void
    {
        $this->cityTranslationMapper->forEach($translations, function ($city, $translation) {
            $this->addTranslation($translation, $city);
        });
    }

    /**
     * Translate divisions using the given collection of translations.
     */
    protected function translateDivisions(Collection $translations): void
    {
        $this->divisionTranslationMapper->forEach($translations, function ($division, $translation) {
            $this->addTranslation($translation, $division);
        });
    }

    /**
     * Add translation for the model.
     *
     * @param Model|Continent|Country|Division|City $model
     */
    protected function addTranslation(array $translation, Model $model): void
    {
        $this->preparedTranslations->push($this->mapTranslation($translation, $model));
    }

    /**
     * Map translation attributes.
     *
     * @param Model $model
     * @param array $translation
     * @return array
     */
    protected function mapTranslation(array $translation, Model $model): array
    {
        return [
            'id' => Translation::generateId(),
            'translatable_id' => $model->getKey(),
            'translatable_type' => $model->getMorphClass(),
            'translatable_attribute' => 'name',
            'value' => $translation['alternate name'],
            'locale' => $translation['isolanguage'],
            'is_archived' => $this->isArchivedTranslation($translation),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    /**
     * Determine whether the translation is archived.
     */
    protected function isArchivedTranslation(array $translation): bool
    {
        if (is_null($translation['isolanguage'])) {
            return true;
        }

        if ($translation['isPreferredName'] === '1') {
            return false;
        }

        if ($translation['isShortName'] === '1') {
            return false;
        }

        if ($translation['isColloquial'] === '1') {
            return true;
        }

        if ($translation['isHistoric'] === '1') {
            return true;
        }

        return false;
    }
}
