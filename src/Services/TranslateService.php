<?php

namespace Nevadskiy\Geonames\Services;

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
     * The translation supplier instance.
     *
     * @var TranslationSupplier
     */
    protected $translationSupplier;

    /**
     * Make a new supply service instance.
     */
    public function __construct(AlternateNameParser $alternateNameParser, TranslationSupplier $translationSupplier)
    {
        $this->alternateNameParser = $alternateNameParser;
        $this->translationSupplier = $translationSupplier;
    }

    /**
     * Get the alternate name parser instance.
     *
     * @return AlternateNameParser
     */
    public function getAlternateNameParser(): AlternateNameParser
    {
        return $this->alternateNameParser;
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
}
