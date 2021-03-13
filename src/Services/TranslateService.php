<?php

namespace Nevadskiy\Geonames\Services;

use Nevadskiy\Geonames\Parsers\AlternateNameDeletesParser;
use Nevadskiy\Geonames\Parsers\AlternateNameParser;
use Nevadskiy\Geonames\Suppliers\Translations\TranslationSupplier;

class TranslateService
{
    /**
     * The alternate name parser instance.
     *
     * @var AlternateNameParser
     */
    protected $alternateNameParser;

    /**
     * The alternate name parser instance.
     *
     * @var AlternateNameParser
     */
    protected $alternateDeletesParser;

    /**
     * The translation supplier instance.
     *
     * @var TranslationSupplier
     */
    protected $translationSupplier;

    /**
     * Make a new supply service instance.
     */
    public function __construct(
        AlternateNameParser $alternateNameParser,
        AlternateNameDeletesParser $alternateDeletesParser,
        TranslationSupplier $translationSupplier
    )
    {
        $this->alternateNameParser = $alternateNameParser;
        $this->alternateDeletesParser = $alternateDeletesParser;
        $this->translationSupplier = $translationSupplier;
    }

    /**
     * Insert dataset translations from the given path.
     *
     * @param string $path
     */
    public function insert(string $path): void
    {
        $this->translationSupplier->insertMany($this->alternateNameParser->each($path));
    }

    /**
     * Modify dataset translations by the given path.
     *
     * @param string $path
     */
    public function modify(string $path): void
    {
        $this->translationSupplier->modifyMany($this->alternateNameParser->each($path));
    }

    /**
     * Delete dataset translations by the given path.
     *
     * @param string $path
     */
    public function delete(string $path): void
    {
        $this->translationSupplier->deleteMany($this->alternateDeletesParser->each($path));
    }
}
