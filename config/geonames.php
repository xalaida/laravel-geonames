<?php

return [
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
    | Geonames resources directory
    |--------------------------------------------------------------------------
    |
    | A directory for geonames meta files and downloads.
    |
    */
    'directory' => storage_path('meta/geonames'),

    /*
    |--------------------------------------------------------------------------
    | Geonames files
    |--------------------------------------------------------------------------
    |
    | Filenames of the geonames resources.
    | More info: http://download.geonames.org/export/dump/
    | TODO: refactor
    |
    */
    'files' => [

        'country_info' => 'countryInfo.txt',

        'all_countries' => 'allCountries/allCountries.txt',

        'alternate_names' => 'alternateNames/alternateNames.txt',

        'countries' => 'countries.php',

        'continents' => 'continents.php',

    ],

    'tables' => [
        Nevadskiy\Geonames\Models\Continent::TABLE,
        Nevadskiy\Geonames\Models\Country::TABLE,
        Nevadskiy\Geonames\Models\Division::TABLE,
        Nevadskiy\Geonames\Models\City::TABLE,
    ],

    /*
    |--------------------------------------------------------------------------
    | Seeding filters.
    |--------------------------------------------------------------------------
    |
    | Specify filters for geonames data seeding.
    |
    */
    'filters' => [

        'continents' => true,

        'countries' => true,

        // TODO: add possibility to remove divisions without cities
        'divisions' => true,

        'cities' => true,

        'min_population' => 0,

        'translations' => true,

        'languages' => ['en', 'es', 'fr', 'it', 'pt', 'pl', 'ru', 'ja', 'zh', 'hi', 'ar', 'bn'],

        'nullable_language' => true,

    ],

    /*
    |--------------------------------------------------------------------------
    | Default geonames suppliers
    |--------------------------------------------------------------------------
    |
    | Override it when you are going to use custom migrations.
    |
    */
    'suppliers' => [
        Nevadskiy\Geonames\Suppliers\ContinentSupplier::class => Nevadskiy\Geonames\Suppliers\ContinentDefaultSupplier::class,
        Nevadskiy\Geonames\Suppliers\CountrySupplier::class => Nevadskiy\Geonames\Suppliers\CountryDefaultSupplier::class,
        Nevadskiy\Geonames\Suppliers\DivisionSupplier::class => Nevadskiy\Geonames\Suppliers\DivisionDefaultSupplier::class,
        Nevadskiy\Geonames\Suppliers\CitySupplier::class => Nevadskiy\Geonames\Suppliers\CityDefaultSupplier::class,
        Nevadskiy\Geonames\Suppliers\Translations\TranslationSupplier::class => Nevadskiy\Geonames\Suppliers\Translations\TranslationDefaultSeeder::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Geonames resources URL
    |--------------------------------------------------------------------------
    |
    | The URL with all geonames resources.
    |
    */
    'resources_url' => 'http://download.geonames.org/export/dump/',
];
