<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Geonames resources directory
    |--------------------------------------------------------------------------
    |
    | A directory for geonames meta files and downloads.
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
    | You can disable translations or specify your own languages list for translations.
    |
    */

    /*
     * Indicates if the translations should be supplied.
     */
    'translations' => true,

    /*
     * Indicates the language list for translations.
     */
    'languages' => ['en', 'es', 'fr', 'de', 'it', 'pt', 'pl', 'ru', 'ja', 'zh', 'hi', 'ar', 'bn'],

    /*
     * Indicates if nullable languages should be supplied.
     * Some geonames alternate names have no defined concrete language.
     * Its can be useful for searching, but it increases the database size.
     */
    'nullable_language' => true,

    /*
    |--------------------------------------------------------------------------
    | Seeders
    |--------------------------------------------------------------------------
    |
    | The list of seeders.
    |
    */

    // TODO: consider adding possibility to detect seeders automatically based on preferred source
    'seeders' => [
        Nevadskiy\Geonames\Seeders\ContinentSeeder::class,
        Nevadskiy\Geonames\Seeders\CountrySeeder::class,
        Nevadskiy\Geonames\Seeders\DivisionSeeder::class,
        Nevadskiy\Geonames\Seeders\CitySeeder::class,
        Nevadskiy\Geonames\Seeders\ContinentTranslationsSeeder::class,
        Nevadskiy\Geonames\Seeders\CountryTranslationsSeeder::class,
        Nevadskiy\Geonames\Seeders\DivisionTranslationsSeeder::class,
        Nevadskiy\Geonames\Seeders\CityTranslationsSeeder::class,
    ],

];
