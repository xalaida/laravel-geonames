<?php

namespace Nevadskiy\Geonames\Seeders;

use Illuminate\Database\Eloquent\Model;

class DivisionTranslationSeeder extends TranslationSeeder
{
    /**
     * {@inheritdoc}
     */
    protected function baseModel(): Model
    {
        return DivisionSeeder::model();
    }
}
