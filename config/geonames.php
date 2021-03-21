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
    | Geonames tables
    |--------------------------------------------------------------------------
    |
    | Specify tables that will be used in the application.
    | By default, there are 4 tables enabled.
    |
    */

    'tables' => [

        'continents' => true,

        'countries' => true,

        'divisions' => true,

        'cities' => true,

    ],

    /*
    |--------------------------------------------------------------------------
    | Default package boot settings
    |--------------------------------------------------------------------------
    |
    | Configure default package settings like loading default migrations,
    | morph mapping for models and others according to personal needs.
    |
    */

    'default_migrations' => true,

    'default_morph_map' => true,

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
    'languages' => ['en', 'es', 'fr', 'it', 'pt', 'pl', 'ru', 'ja', 'zh', 'hi', 'ar', 'bn'],

    /*
     * Indicates if nullable languages should be supplied.
     * Some geonames alternate names have no defined concrete language.
     * Its can be useful for searching, but it increases the database size.
     */
    'nullable_language' => true,

    /*
    |--------------------------------------------------------------------------
    | Default geonames suppliers
    |--------------------------------------------------------------------------
    |
    | Override it when you are going to use custom migrations
    | with own custom insert, update and delete logic.
    |
    */
    'suppliers' => [
        Nevadskiy\Geonames\Suppliers\ContinentSupplier::class => Nevadskiy\Geonames\Suppliers\ContinentDefaultSupplier::class,
        Nevadskiy\Geonames\Suppliers\CountrySupplier::class => Nevadskiy\Geonames\Suppliers\CountryDefaultSupplier::class,
        Nevadskiy\Geonames\Suppliers\DivisionSupplier::class => Nevadskiy\Geonames\Suppliers\DivisionDefaultSupplier::class,
        Nevadskiy\Geonames\Suppliers\CitySupplier::class => Nevadskiy\Geonames\Suppliers\CityDefaultSupplier::class,
        Nevadskiy\Geonames\Suppliers\Translations\TranslationSupplier::class => Nevadskiy\Geonames\Suppliers\Translations\TranslationDefaultSupplier::class,
    ],

];
