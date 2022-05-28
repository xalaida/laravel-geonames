<?php

namespace Nevadskiy\Geonames\Seeders;

class CityTranslationSeeder extends TranslationSeeder
{
    /**
     * {@inheritdoc}
     */
    public static function baseModel(): string
    {
        return CitySeeder::model();
    }
}
