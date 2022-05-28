# 🌎 Laravel Geonames

**Currently, the work in progress. It will receive updates with possible breaking changes. Not recommended using in production environments yet.**

The package provides geonames seeders for a Laravel application.

## 🗒️ Description

The package is useful for applications that rely on the geo data.

It allows seeding the database using the [geonames](https://www.geonames.org/) service.

By default, it provides 4 models: `Continent`, `Country`, `Division`, `City` and also allows you to add translations for them.

Translations are powered by the [nevadskiy/laravel-translatable](https://github.com/nevadskiy/laravel-translatable) package.

The package also keeps the data **up-to-date** by fetching daily modifications provided by the [geonames](https://www.geonames.org/) service and using them to synchronize your own database.

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

Publish the geonames files.

```
php artisan ...
```

It will publish the package migrations and models.

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

### Filtering

To reduce the database size, you can set up filters for seeding only those geo data that you really need in your application.

For example, you can set the minimum population for the city. All cities with smaller population will not be imported.

Filters can be set up in the config file by the `geonames.filters` path.

### Memory leaks

...

### Schedule updates

Add the following code to your console kernel (`app/Console/Kernel.php`) if you want to receive geonames daily updates.

Geonames daily updates are published at 3:00 in the UTC time zone, so to be sure that they are already available, it is recommended to run the command a bit later.

```php
protected function schedule(Schedule $schedule)
{
    $schedule->command('geonames:daily-update')->dailyAt('4:00');
}
```

> Note that time is specified for the `UTC` timezone.

### Syncing

If you missed some daily updates or just decided to change seeder filters, you can force the sync process.

```bash
php artisan geonames:sync
```

This command will create missing records, remove redundant ones, and updated modified ones according to the current dataset.

### Customization

If you want to customize migrations or data that should be imported, also publish seeders and the config file.

Publish the package config.

```bash
php artisan vendor:publish --tag=geonames-config
```

Publish the package seeders.

```bash
php artisan ...
```

After publishing the seeders, make sure that you have specified those classes instead of original ones in the config file by the `geonames.seeders` path.

#### Removing unnecessary fields from database tables

You can reduce the database size by removing unnecessary fields from the database tables.

To do that, just remove those fields from the migrations and the seeder will handle in automatically.

> Note that the `synced_at` and `geoname_id` fields are needed to synchronize data, so they should be present.

#### Custom seeders

For a more significant change in the structure, you can add your own seeders or extend existing ones.

Each seeder must implement the `Nevadskiy\Geonames\Seeders\Seeder` interface.

All seeders that are specified in the config for the `geonames.seeders` path will be executed one after another in the specified order.

#### Attributes mapping

To add custom fields to the table, you also need to tell the seeder how to fill those fields using the `mapAttributes` method.

For example, if you want to use UUIDs as primary keys, you can extend the original seeder as following:

```php
<?php

namespace App\Seeders;

use App\Models\Geo\City;
use Nevadskiy\Geonames\Seeders\CitySeeder as Seeder;

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
            'id' => City::generateUuid(),
        ]);
    }
}
```

### Nova resources

...

### Translations

To use translations you need to install the [nevadskiy/laravel-translatable](https://github.com/nevadskiy/laravel-translatable) package.

Read its documentation to learn more about how it works. You can also use it to handle translations of other models.

Otherwise, you still can use the package without translations, just simply remove the following:

- translation seeders from the config file
- translation migrations
- the `HasTranslations` trait from published models

## 📑 Changelog

Please see [CHANGELOG](.github/CHANGELOG.md) for more information what has changed recently.

## ☕ Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for more information.

## 🔓 Security

If you discover any security related issues, please [e-mail me](mailto:nevadskiy@gmail.com) instead of using the issue tracker.

## 📜 License

The MIT License (MIT). Please see [LICENSE](LICENSE.md) for more information.

## 🔨 To Do
 
- [ ] add downloader decorator that uses already downloaded files instead of updating them even if size is changed (prevent de-sync at UTC 00:00 when new file is uploaded during seed process). 
- [ ] refactor seeders to use DI parser and downloader.
- [ ] add possibility to define download sources (only cities, no-countries or archive for specific countries).
- [ ] consider local seeding with testing data
- [ ] add classic structure according to `https://www.oasis-open.org/committees/ciq/download.shtml` (similar to google places API)
