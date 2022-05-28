<?php

namespace Nevadskiy\Geonames\Seeders;

class CountryTranslationSeeder extends TranslationSeeder
{
    /**
     * {@inheritdoc}
     */
    public static function baseModel(): string
    {
        return CountrySeeder::model();
    }
}
