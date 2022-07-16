<?php

namespace Nevadskiy\Geonames\Seeders;

class ContinentTranslationSeeder extends TranslationSeeder
{
    /**
     * {@inheritdoc}
     */
    public static function translatableModel(): string
    {
        return ContinentSeeder::model();
    }
}
