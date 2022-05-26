<?php

namespace Nevadskiy\Geonames\Seeders;

use Illuminate\Database\Eloquent\Model;

class CityTranslationSeeder extends TranslationSeeder
{
    /**
     * {@inheritdoc}
     */
    protected function baseModel(): Model
    {
        return CitySeeder::model();
    }
}
