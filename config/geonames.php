<?php

use Nevadskiy\Geonames\Definitions\FeatureCode;

return [

    /*
    |--------------------------------------------------------------------------
    | Seeders
    |--------------------------------------------------------------------------
    |
    | List of seeders that will be used to populate and update the database.
    | They will run one after the other so the order is important.
    |
    */

    'seeders' => [
        Nevadskiy\Geonames\Seeders\ContinentSeeder::class,
        Nevadskiy\Geonames\Seeders\ContinentTranslationSeeder::class,
        Nevadskiy\Geonames\Seeders\CountrySeeder::class,
        Nevadskiy\Geonames\Seeders\CountryTranslationSeeder::class,
        Nevadskiy\Geonames\Seeders\DivisionSeeder::class,
        Nevadskiy\Geonames\Seeders\DivisionTranslationSeeder::class,
        Nevadskiy\Geonames\Seeders\CitySeeder::class,
        Nevadskiy\Geonames\Seeders\CityTranslationSeeder::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Seeder filters
    |--------------------------------------------------------------------------
    |
    | Specify filters for geonames data seeding.
    |
    */

    'filters' => [

        'cities' => [
            'min_population' => 500,

            'feature_codes' => [
                FeatureCode::PPL,
                FeatureCode::PPLC,
                FeatureCode::PPLA,
                FeatureCode::PPLA2,
                FeatureCode::PPLA3,
                FeatureCode::PPLG,
                FeatureCode::PPLS,
                FeatureCode::PPLX,
            ]
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Translations
    |--------------------------------------------------------------------------
    |
    | Translations are powered on the "nevadskiy/laravel-translatable" package.
    |
    */

    'translations' => [

        /*
         * The list of locales for which translations should be seeded.
         */
        'locales' => ['en', 'es', 'fr', 'de', 'it', 'pt', 'pl', 'uk', 'ru', 'ja', 'zh', 'hi', 'ar'],

        /*
         * Indicates if translations with a nullable locale should be seeded.
         * These type of translations cannot be used for display but can be used for searching.
         */
        'nullable_locale' => true,

    ],

    /*
    |--------------------------------------------------------------------------
    | Geonames resources directory
    |--------------------------------------------------------------------------
    |
    | A temporary directory for geonames meta files and downloads.
    | It can be added to the .gitignore file.
    |
    */

    'directory' => storage_path('meta/geonames'),

];
