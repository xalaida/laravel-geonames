<?php

namespace Nevadskiy\Geonames\Suppliers\Translations;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Nevadskiy\Geonames\Geonames;
use Nevadskiy\Geonames\Models\Continent;
use Nevadskiy\Geonames\Models\Country;
use Nevadskiy\Geonames\Models\Division;
use Nevadskiy\Geonames\Models\City;
use Nevadskiy\Geonames\Support\Batch\Batch;
use Nevadskiy\Translatable\Models\Translation;

class TranslationDefaultSupplier implements TranslationSupplier
{
    /**
     * The geonames instance.
     *
     * @var Geonames
     */
    private $geonames;

    /**
     * The translation mapper instance.
     *
     * @var TranslationMapper
     */
    private $translationMapper;

    /**
     * A batch of source translations.
     *
     * @var Batch
     */
    protected $sourceBatch;

    /**
     * The batch of prepared translations.
     *
     * @var Batch
     */
    protected $insertBatch;

    /**
     * Make a new seeder instance.
     */
    public function __construct(Geonames $geonames, TranslationMapper $translationMapper)
    {
        $this->geonames = $geonames;
        $this->translationMapper = $translationMapper;
        $this->sourceBatch = $this->makeSourceBatch();
        $this->insertBatch = $this->makeInsertBatch();
    }

    /**
     * @inheritDoc
     */
    public function insertMany(iterable $data): void
    {
        foreach ($data as $item) {
            $this->insert($item);
        }
    }

    /**
     * @inheritDoc
     */
    public function modifyMany(iterable $data): void
    {
        // TODO: Implement modifyMany() method.
    }

    public function deleteMany(iterable $data): void
    {
        // TODO: Implement deleteMany() method.
    }

    protected function insert(array $translation): void
    {
        if ($this->shouldSupply($translation)) {
            $this->sourceBatch->push($translation);
        }
    }

    /**
     * Make a batch instance for source translations.
     *
     * @return Batch
     */
    protected function makeSourceBatch(): Batch
    {
        return new Batch(function (array $items) {
            $this->translate(collect($items));
        }, 1000);
    }

    /**
     * Translate the database items using the given translations.
     *
     * @param Collection $translations
     */
    protected function translate(Collection $translations): void
    {
        $this->translationMapper->forEach($translations, function ($model, $translation) {
            $this->addTranslation($translation, $model);
        });
    }

    /**
     * Determine whether the translation item should be supplied.
     */
    protected function shouldSupply(array $translation): bool
    {
        return $this->geonames->isLanguageAllowed($translation['isolanguage']);
    }

    /**
     * Make a batch instance for prepared translations to be inserted.
     *
     * @return Batch
     */
    protected function makeInsertBatch(): Batch
    {
        return new Batch(function (array $items) {
            DB::table('translations')->insert($items);
        }, 1000);
    }

    /**
     * Add translation for the model.
     *
     * @param Model|Continent|Country|Division|City $model
     */
    protected function addTranslation(array $translation, Model $model): void
    {
        $this->insertBatch->push($this->mapTranslation($translation, $model));
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
     * Determine whether the translation should be archived.
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
