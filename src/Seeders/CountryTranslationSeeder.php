<?php

namespace Nevadskiy\Geonames\Seeders;

class CountryTranslationSeeder extends TranslationSeeder
{
    /**
     * {@inheritdoc}
     */
    public static function translatableModel(): string
    {
        return CountrySeeder::model();
    }
}
