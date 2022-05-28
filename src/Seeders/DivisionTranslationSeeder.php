<?php

namespace Nevadskiy\Geonames\Seeders;

class DivisionTranslationSeeder extends TranslationSeeder
{
    /**
     * {@inheritdoc}
     */
    public static function baseModel(): string
    {
        return DivisionSeeder::model();
    }
}
