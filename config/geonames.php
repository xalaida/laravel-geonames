<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Geonames resources directory
    |--------------------------------------------------------------------------
    |
    | A directory for geonames meta files and downloads.
    | It can be added to the .gitignore file.
    |
    */

    'directory' => storage_path('meta/geonames'),

    /*
    |--------------------------------------------------------------------------
    | Geonames source
    |--------------------------------------------------------------------------
    |
    | You can choose appropriate data source for seeding as one of
    | SOURCE_ALL_COUNTRIES, SOURCE_SINGLE_COUNTRY or SOURCE_ONLY_CITIES.
    |
    | - SOURCE_ALL_COUNTRIES has the biggest database size but contains the most items.
    |
    | - SOURCE_SINGLE_COUNTRY contains only items that belongs to the specific country.
    | You can specify which country (or countries) you are going to seed in filters array by ISO code (e.g. US, GB).
    |
    | - SOURCE_ONLY_CITIES has the smallest size and contains only cities.
    | Other tables (continents, countries, divisions) will not be seeded.
    |
    | More info: http://download.geonames.org/export/dump/
    |
    */

    'source' => Nevadskiy\Geonames\Services\DownloadService::SOURCE_ALL_COUNTRIES,

    /*
    |--------------------------------------------------------------------------
    | Seed filters
    |--------------------------------------------------------------------------
    |
    | Specify filters for geonames data seeding.
    |
    */

    'filters' => [

        'countries' => ['*'],

        'population' => 500,

    ],

    /*
    |--------------------------------------------------------------------------
    | Translations
    |--------------------------------------------------------------------------
    |
    | Set up translations configurations.
    | You can disable translations or specify your own language list for translations.
    |
    */

    'translations' => [
        /*
         * Indicates the locale list for translations.
         * Also, some translations have no defined concrete locale (nullable locale) that can be used for searching.
         * TODO: refactor this to remove nullable from array.
         */
        'locales' => ['en', 'es', 'fr', 'de', 'it', 'pt', 'pl', 'uk', 'ru', 'ja', 'zh', 'hi', 'ar', 'bn', null],
    ],

    /*
    |--------------------------------------------------------------------------
    | Seeders
    |--------------------------------------------------------------------------
    |
    | The list of seeders.
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

];
