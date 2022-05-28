<?php

namespace Nevadskiy\Geonames\Seeders;

class ContinentTranslationSeeder extends TranslationSeeder
{
    /**
     * {@inheritdoc}
     */
    public static function baseModel(): string
    {
        return ContinentSeeder::model();
    }
}
