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
    | Geonames source.
    |--------------------------------------------------------------------------
    |
    | TODO: add description
    | More info: http://download.geonames.org/export/dump/
    |
    */

    'source' => Nevadskiy\Geonames\Services\DownloadService::SOURCE_ALL_COUNTRIES,

    /*
    |--------------------------------------------------------------------------
    | Seeding filters.
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
    | Geonames tables.
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
    | TODO: add description
    |
    */

    /*
     * Indicates if the translations should be supplied.
     */
    'translations' => true,

    /*
     * Indicates the languages list that should translate into.
     */
    'languages' => ['en', 'es', 'fr', 'it', 'pt', 'pl', 'ru', 'ja', 'zh', 'hi', 'ar', 'bn'],

    /*
     * Indicates if nullable languages should be supplied.
     * Can be useful for searching, but increase the database size.
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
