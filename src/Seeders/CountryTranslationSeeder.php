<?php

namespace Nevadskiy\Geonames\Seeders;

use Illuminate\Database\Eloquent\Model;

class CountryTranslationSeeder extends TranslationSeeder
{
    /**
     * {@inheritdoc}
     */
    protected function baseModel(): Model
    {
        return ContinentSeeder::model();
    }
}
