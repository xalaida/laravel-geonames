# 🌎 Laravel Geonames

[![Stand With Ukraine](https://raw.githubusercontent.com/vshymanskyy/StandWithUkraine/main/banner-direct-single.svg)](https://stand-with-ukraine.pp.ua)

[![Latest Stable Version](https://poser.pugx.org/nevadskiy/laravel-geonames/v)](https://packagist.org/packages/nevadskiy/laravel-geonames)
[![Tests](https://img.shields.io/github/workflow/status/nevadskiy/laravel-geonames/Tests?label=tests)](https://packagist.org/packages/nevadskiy/laravel-geonames)
[![Code Coverage](https://codecov.io/gh/nevadskiy/laravel-geonames/branch/master/graphs/badge.svg?branch=master)](https://packagist.org/packages/nevadskiy/laravel-geonames)
[![License](https://img.shields.io/packagist/l/nevadskiy/laravel-geonames)](https://packagist.org/packages/nevadskiy/laravel-geonames)

The package allows you to populate your database using the [geonames](https://www.geonames.org/) dataset.

## 🗒️ Description

The package is useful for applications that rely on the geo data.

By default, it provides 4 models: `Continent`, `Country`, `Division`, `City` and translations for them.

The translations are powered by the [nevadskiy/laravel-translatable](https://github.com/nevadskiy/laravel-translatable) package.

The package also keeps the data **up-to-date** by fetching daily modifications provided by the [geonames](https://www.geonames.org/) service and uses them to synchronize your own database.

## 🔌 Installation

```bash
composer require nevadskiy/laravel-geonames
```

If you are going to use translations, you also need to install an additional package.

```bash
composer require nevadskiy/laravel-translatable
```

## ✅ Requirements

- Laravel `8.0` or newer
- PHP `7.3` or newer

## 🔨 Usage

Publish the package resources using the command:

```bash
php artisan vendor:publish --tag=geonames-migrations --tag=geonames-models
```

### Seeding

Before seeding, make sure you run the database migrations.

```bash
php artisan migrate
```

Then, run the seeding process.

```bash
php artisan geonames:seed
```

It will download geonames resources and insert the dataset into your database.

> Note that the seeding process may take some time. On average, it takes about 40 minutes (without downloading time) to seed the full dataset with translations.

> If you have issues with memory leaks during seeding, check out [this section](#memory-leaks).

### Schedule updates

Add the following code to the `app/Console/Kernel.php` file if you want to receive geonames daily updates.

Geonames daily updates are published at 3:00 in the UTC time zone, so to be sure that they are already available, it is recommended to run the command a bit later.

```php
protected function schedule(Schedule $schedule)
{
    $schedule->command('geonames:daily-update')->dailyAt('4:00');
}
```

> Note that time is specified for the `UTC` timezone.

### Syncing

If you missed some daily updates or just decided to change seeder filters, you can sync your database records according to the current geonames dataset.

```bash
php artisan geonames:sync
```

This command will create missing records, remove redundant ones, and updated modified ones according to the current dataset.

> Note that the `geoname_id` and `alternate_name_id` fields is required to synchronize data.

### Customization

If you want to customize migrations or data that should be imported, you can simply do this by overriding the default seeders.

To do that, publish the package seeders using command:

```bash
php artisan vendor:publish --tag=geonames-seeders
```

Then publish the package config and specify those seeders there:

```bash
php artisan vendor:publish --tag=geonames-config 
```

```php
# config/geonames.php

'seeders' => [
    Database\Seeders\Geo\ContinentSeeder::class,
    Database\Seeders\Geo\ContinentTranslationSeeder::class,
    Database\Seeders\Geo\CountrySeeder::class,
    Database\Seeders\Geo\CountryTranslationSeeder::class,
    Database\Seeders\Geo\DivisionSeeder::class,
    Database\Seeders\Geo\DivisionTranslationSeeder::class,
    Database\Seeders\Geo\CitySeeder::class,
    Database\Seeders\Geo\CityTranslationSeeder::class,
]
```

### Filtering

To reduce the database size, you can set up filters for seeding only those geo data that you really need in your application.

For example, you can set the minimum population for the city. All cities with smaller population will not be imported.

To do that, override the `$minPopulation` property in the `CitySeeder` class.

To have full control over this behaviour, override the `filter` method of the seeder.

#### Attributes mapping

To add custom fields to the table, you also need to tell the seeder how to fill those fields using the `mapAttributes` method.

The `mapAttributes` method should return all attributes of the database record, including timestamps, because model events will not be fired during seeding process since the package uses a bulk insert strategy.
However, all model casts and mutators will be applied as usual.

For example, if you want to use UUIDs as primary keys, you can extend the original seeder as following:

```php
<?php

namespace Database\Seeders\Geo;

use App\Models\Geo\City;
use Illuminate\Support\Str;use Nevadskiy\Geonames\Seeders\CitySeeder as Seeder;

class CitySeeder extends Seeder
{
    /**
     * {@inheritdoc}
     */
    protected static $model = City::class;

    /**
     * {@inheritdoc}
     */
    protected function mapAttributes(array $record): array
    {
        return array_merge(parent::mapAttributes($record), [
            'id' => (string) Str::uuid(),
        ]);
    }
}
```

[//]: # (TODO: doc `updatable` method)

#### Custom seeders

For a more significant change in the structure, you can add your own seeders or extend existing ones.

Each seeder must implement the `Nevadskiy\Geonames\Seeders\Seeder` interface.

All seeders that are specified in the `geonames` config file will be executed one by one in the specified order.

[//]: # (TODO: doc seeder hooks: `loadResourcesBeforeMapping`, `loadResourcesBeforeChunkMapping`)

### Translations

To use translations you need to install the [nevadskiy/laravel-translatable](https://github.com/nevadskiy/laravel-translatable) package.

Read its [documentation](https://github.com/nevadskiy/laravel-translatable/wiki) to learn more about how it works. You can also use it to handle translations of other models.

Otherwise, you still can use the package without translations, just simply remove the following:

- translation migrations
- translation seeders (from the `geonames` config file as well)
- the `HasTranslations` trait and `translatable` prop from published models

### Memory leaks

One of the most popular issues associated with seeding large amounts of data is a memory leak.

This package reads files using PHP generators and [lazy collections](https://laravel.com/docs/9.x/collections#lazy-collection-introduction) to avoid loading the entire file into memory.

However, there are packages that log database queries and model events during long-running commands to memory, which leads to memory leaks.

There are instructions on how to avoid memory leaks when working with the following packages:

#### [Laravel Ignition](https://github.com/spatie/laravel-ignition)

Publish flare config using `php artisan vendor:publish --tag=flare-config`.

Set `report_query_bindings` to `false` in the `flare.php` config file as following:

```php
'flare_middleware' => [
    ...
    AddQueries::class => [
        'maximum_number_of_collected_queries' => 200,
        'report_query_bindings' => false,
    ],
    ...
]
```

#### [Laravel Telescope](https://github.com/laravel/telescope)

Update the `telescope.php` config file as following:

```php
'ignore_commands' => [
    'geonames:seed',
    'geonames:daily-update',
    'geonames:sync',
]
```

## 📑 Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## ☕ Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for more information.

## 📜 License

The MIT License (MIT). Please see [LICENSE](LICENSE) for more information.
