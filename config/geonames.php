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

//    /*
//    |--------------------------------------------------------------------------
//    | Geonames files
//    |--------------------------------------------------------------------------
//    |
//    | Filenames of the geonames resources.
//    | More info: http://download.geonames.org/export/dump/
//    | TODO: refactor
//    |
//    */
//    'files' => [
//
//        'country_info' => 'countryInfo.txt',
//
//        'all_countries' => 'allCountries/allCountries.txt',
//
//        'alternate_names' => 'alternateNames/alternateNames.txt',
//
//        'countries' => 'countries.php',
//
//        'continents' => 'continents.php',
//
//    ],

    /*
    |--------------------------------------------------------------------------
    | Geonames source.
    |--------------------------------------------------------------------------
    |
    | TODO: add description
    |
    */

    'source' => Nevadskiy\Geonames\Services\DownloadService::SOURCE_ALL_COUNTRIES,

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
    | Seeding filters.
    |--------------------------------------------------------------------------
    |
    | Specify filters for geonames data seeding.
    |
    */

    'filters' => [

        'countries' => ['*'],

        'population' => 0,

    ],


    'CASES' => [
        1 => [
            'config' => [
                'source' => 'all_countries',
                'filters' => [
                    'countries' => ['UA'],
                    'population' => 0,
                ],
                'tables' => [
                    'continents' => true,
                    'countries' => true,
                    'divisions' => true,
                    'cities' => true,
                ],
            ],
            'assert' => [
                'file' => 'all_countries',
                'tables' => ['countries', 'divisions', 'cities'],
                'data' => ['1 country UA', '27 UA divisions', 'All UA cities']
            ]
        ],
        2 => [
            'config' => [
                'source' => 'single_country.zip',
                'filters' => [
                    'countries' => ['UA'],
                    'population' => 500,
                ],
                'tables' => [
                    'continents' => true,
                    'countries' => true,
                    'divisions' => true,
                    'cities' => true,
                ],
            ],
            'assert' => [
                'file' => 'UA.zip',
                'tables' => ['countries', 'divisions', 'cities'],
                'data' => ['1 country UA', '27 UA divisions', 'All UA cities']
            ]
        ],
        3 => [
            'config' => [
                'source' => 'single_country.zip',
                'filters' => [
                    'countries' => ['UA', 'PL'],
                    'population' => 500,
                ],
                'tables' => [
                    'continents' => true,
                    'countries' => true,
                    'divisions' => true,
                    'cities' => true,
                ],
            ],
            'assert' => [
                'file' => 'UA.zip, PL.zip',
                'tables' => ['countries', 'divisions', 'cities'],
                'data' => ['2 country (UA, PL)', '27 UA divisions + N PL divisions', 'All (UA, PL) cities']
            ]
        ],
        4 => [
            'config' => [
                'source' => 'auto',
                'filters' => [
                    'countries' => ['UA'],
                    'population' => 500,
                ],
                'tables' => [
                    'continents' => false,
                    'countries' => false,
                    'divisions' => true,
                    'cities' => true,
                ],
            ],
            'assert' => [
                'file' => 'UA.zip',
                'tables' => ['divisions', 'cities'],
                'data' => ['1 country UA', '27 UA divisions', 'All UA cities']
            ]
        ]
    ],

    /*
    |--------------------------------------------------------------------------
    | Translations
    |--------------------------------------------------------------------------
    |
    | TODO: add description
    |
    */

    'translations' => true,

    'languages' => ['en', 'es', 'fr', 'it', 'pt', 'pl', 'ru', 'ja', 'zh', 'hi', 'ar', 'bn'],

    'nullable_language' => true,

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

//    /*
//    |--------------------------------------------------------------------------
//    | Geonames resources URL
//    |--------------------------------------------------------------------------
//    |
//    | The URL with all geonames resources.
//    |
//    */
//    'resources_url' => 'http://download.geonames.org/export/dump/',

];
