<?php

namespace Nevadskiy\Geonames\Suppliers\Translations;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Nevadskiy\Geonames\Geonames;
use Nevadskiy\Geonames\Models\City;
use Nevadskiy\Geonames\Models\Continent;
use Nevadskiy\Geonames\Models\Country;
use Nevadskiy\Geonames\Models\Division;
use Nevadskiy\Geonames\Support\Batch\Batch;
use Nevadskiy\Translatable\Models\Translation;

class TranslationDefaultSupplier implements TranslationSupplier
{
    /**
     * The geonames instance.
     *
     * @var Geonames
     */
    protected $geonames;

    /**
     * The translation mapper instance.
     *
     * @var TranslationMapper
     */
    protected $translationMapper;

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
    }

    /**
     * {@inheritdoc}
     */
    public function insertMany(iterable $data): void
    {
        $this->initInsert();

        foreach ($data as $translation) {
            $this->addToSourceIfProcessable($translation);
        }

        $this->commitInsert();
    }

    /**
     * {@inheritdoc}
     */
    public function modifyMany(iterable $data): void
    {
        $this->initModify();

        foreach ($data as $translation) {
            $this->addToSourceIfProcessable($translation);
        }

        $this->commitModify();
    }

    /**
     * {@inheritdoc}
     */
    public function deleteMany(iterable $data): void
    {
        $this->initDelete();

        foreach ($data as $translation) {
            $this->addToSource($translation);
        }

        $this->commitDelete();
    }

    /**
     * Init the insert process.
     */
    protected function initInsert(): void
    {
        $this->sourceBatch = new Batch(function (array $items) {
            $this->insert(collect($items));
        }, 1000);

        $this->insertBatch = new Batch(function (array $items) {
            DB::table('translations')->insert($items);
        }, 1000);
    }

    /**
     * Commit the insert process.
     */
    protected function commitInsert(): void
    {
        $this->sourceBatch->commit();
        $this->insertBatch->commit();
    }

    /**
     * Insert given translations into the database.
     */
    protected function insert(Collection $translations): void
    {
        $this->translationMapper->forEach($translations, function ($model, $translation) {
            $this->addTranslation($translation, $model);
        });
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
     * Init the modify process.
     */
    protected function initModify(): void
    {
        $this->sourceBatch = new Batch(function (array $items) {
            $this->modify(collect($items));
        }, 1000);
    }

    /**
     * Commit the modify process.
     */
    protected function commitModify(): void
    {
        $this->sourceBatch->commit();
    }

    /**
     * Update the database items using the given translations.
     */
    protected function modify(Collection $translations): void
    {
        $this->translationMapper->forEach($translations, function ($model, $translation) {
            $this->updateTranslation($translation, $model);
        });
    }

    /**
     * Update translation of the model.
     */
    protected function updateTranslation(array $translation, Model $model): void
    {
        $translation = Translation::query()->updateOrCreate([
            'translatable_id' => $model->getKey(),
            'translatable_type' => $model->getMorphClass(),
            'translatable_attribute' => 'name',
            'locale' => $translation['isolanguage'],
            'value' => $translation['alternate name'],
        ], [
            'is_archived' => $this->isArchivedTranslation($translation),
        ]);

        if ($translation->wasRecentlyCreated) {
            echo "Translation {$translation->value} has been added to {$translation->translatable_type}\n";
        } else {
            echo "Translation {$translation->value} has been updated in {$translation->translatable_type}\n";
        }
    }

    /**
     * Delete the given translations from the database.
     */
    protected function delete(Collection $translations): void
    {
        $this->translationMapper->forEach($translations, function ($model, $translation) {
            $this->deleteTranslation($translation, $model);
        });
    }

    /**
     * Init the delete process.
     */
    protected function initDelete(): void
    {
        $this->sourceBatch = new Batch(function (array $items) {
            $this->delete(collect($items));
        }, 1000);
    }

    /**
     * Commit the delete process.
     */
    protected function commitDelete(): void
    {
        $this->sourceBatch->commit();
    }

    /**
     * Delete translation from the model.
     *
     * @param Model|Continent|Country|Division|City $model
     */
    protected function deleteTranslation(array $translation, Model $model): void
    {
        // If duplicated translation has been deleted, we dont need to delete all translations,
        // so we limit them to 1 and order by archived translations first.
        $deleted = Translation::query()
            ->where([
                'translatable_id' => $model->getKey(),
                'translatable_type' => $model->getMorphClass(),
                'translatable_attribute' => 'name',
                'value' => $translation['alternate name'],
            ])
            ->orderBy('is_archived')
            ->limit(1)
            ->delete();

        if ($deleted) {
            echo "Deleted {$translation['alternate name']} from the model {$model->getMorphClass()}";
        }
    }

    /**
     * Add the given translation to the source batch if the translation can be supplied.
     */
    protected function addToSourceIfProcessable(array $translation): void
    {
        if ($this->shouldSupply($translation)) {
            $this->addToSource($translation);
        }
    }

    /**
     * Add the given translation to the source batch.
     */
    protected function addToSource(array $translation): void
    {
        $this->sourceBatch->push($translation);
    }

    /**
     * Determine if the translation item should be supplied.
     */
    protected function shouldSupply(array $translation): bool
    {
        return $this->geonames->isLanguageAllowed($translation['isolanguage']);
    }

    /**
     * Determine if the translation should be archived.
     */
    protected function isArchivedTranslation(array $translation): bool
    {
        if (is_null($translation['isolanguage'])) {
            return true;
        }

        if ($translation['isolanguage'] === 'en') {
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
