<?php

namespace Nevadskiy\Geonames\Seeders;

class DivisionTranslationSeeder extends TranslationSeeder
{
    /**
     * {@inheritdoc}
     */
    public static function translatableModel(): string
    {
        return DivisionSeeder::model();
    }
}
