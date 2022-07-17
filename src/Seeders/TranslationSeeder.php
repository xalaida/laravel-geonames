<?php

namespace Nevadskiy\Geonames\Seeders;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\LazyCollection;
use Nevadskiy\Geonames\Reader\AlternateNamesDeletesReader;
use Nevadskiy\Geonames\Reader\AlternateNamesReader;
use Nevadskiy\Geonames\Reader\Reader;
use Nevadskiy\Geonames\Services\DownloadService;
use RuntimeException;

abstract class TranslationSeeder extends BaseSeeder
{
    /**
     * The locale list.
     *
     * @var array
     */
    protected $locales = ['*'];

    /**
     * Indicates if a nullable locale is allowed.
     *
     * @var array
     */
    protected $nullableLocale = true;

    /**
     * The parent model list for which translations are stored.
     *
     * @var array
     */
    protected $translatableModels = [];

    /**
     * Make a new seeder instance.
     */
    public function __construct(DownloadService $downloadService, Reader $reader)
    {
        parent::__construct($downloadService, $reader);
        $this->locales = config('geonames.translations.locales');
        $this->nullableLocale = config('geonames.translations.nullable_locale');
    }

    /**
     * @inheritdoc
     */
    public function getSyncKeyName(): string
    {
        return 'alternate_name_id';
    }

    /**
     * Get a base model class for which translations are stored.
     */
    abstract public static function translatableModel(): string;

    /**
     * Get the base model instance of the seeder.
     */
    protected function newTranslatableModel(): Model
    {
        $model = static::translatableModel();

        if (! is_a($model, Model::class, true)) {
            throw new RuntimeException(sprintf('The seeder model "%s" must extend the base Eloquent model', $model));
        }

        return new $model();
    }

    /**
     * Get a query of model translations.
     */
    protected function query(): Builder
    {
        return $this->newTranslatableModel()
            ->translations()
            ->getModel()
            ->newQuery();
    }

    /**
     * {@inheritdoc}
     */
    protected function getRecords(): iterable
    {
        return (new AlternateNamesReader($this->reader))->getRecords(
            $this->downloadService->downloadAlternateNames()
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function getDailyModificationRecords(): iterable
    {
        return (new AlternateNamesReader($this->reader))->getRecords(
            $this->downloadService->downloadDailyAlternateNamesModifications()
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function getDailyDeleteRecords(): iterable
    {
        return (new AlternateNamesDeletesReader($this->reader))->getRecords(
            $this->downloadService->downloadDailyAlternateNamesDeletes()
        );
    }

    /**
     * Load resources before record attributes mapping.
     */
    protected function loadResourcesBeforeChunkMapping(LazyCollection $records): void
    {
        $this->translatableModels = $this->newTranslatableModel()
            ->newQuery()
            ->whereIn('geoname_id', $records->pluck('geonameid')->unique())
            ->pluck('id', 'geoname_id')
            ->all();
    }

    /**
     * Unload resources after record attributes mapping.
     */
    protected function unloadResourcesAfterChunkMapping(LazyCollection $records): void
    {
        $this->translatableModels = [];
    }

    /**
     * Determine if the given record should be seeded.
     */
    protected function filter(array $record): bool
    {
        return isset($this->translatableModels[$record['geonameid']])
            && $this->isSupportedLocale($record['isolanguage']);
    }

    /**
     * Determine if the given locale is supported.
     */
    protected function isSupportedLocale(?string $locale): bool
    {
        if (is_null($locale)) {
            return $this->nullableLocale;
        }

        if ($this->isWildcardLocale()) {
            return true;
        }

        return in_array($locale, $this->locales, true);
    }

    /**
     * Determine if the locale list is a wildcard.
     */
    protected function isWildcardLocale(): bool
    {
        return count($this->locales) === 1 && $this->locales[0] === '*';
    }

    /**
     * Map fields to the model attributes.
     */
    protected function mapAttributes(array $record): array
    {
        return array_merge([
            'name' => $record['alternate name'],
            'is_preferred' => $record['isPreferredName'] ?: false,
            'is_short' => $record['isShortName'] ?: false,
            'is_colloquial' => $record['isColloquial'] ?: false,
            'is_historic' => $record['isHistoric'] ?: false,
            'locale' => $record['isolanguage'],
            'alternate_name_id' => $record['alternateNameId'],
            'created_at' => now(),
            'updated_at' => now(),
        ], $this->mapRelation($record));
    }

    /**
     * Map the relation attributes of the record.
     */
    protected function mapRelation(array $record): array
    {
        return [
            $this->getTranslationForeignKeyName() => $this->translatableModels[$record['geonameid']],
        ];
    }

    /**
     * Get a foreign key name of the translation model.
     */
    protected function getTranslationForeignKeyName(): string
    {
        return $this->newTranslatableModel()
            ->translations()
            ->getForeignKeyName();
    }

    /**
     * Get a sync key by the given record.
     */
    protected function getSyncKeyByRecord(array $record): int
    {
        return $record['alternateNameId'];
    }

    /**
     * @inheritdoc
     */
    protected function updatable(): array
    {
        return [
            $this->getTranslationForeignKeyName(),
            'name',
            'is_preferred',
            'is_short',
            'is_colloquial',
            'is_historic',
            'locale',
            'updated_at',
        ];
    }
}
