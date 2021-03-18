# 🌎 Laravel Geonames

**Currently the work in progress. It will receive updates with possible breaking changes. Not recommended using in production environments yet.**

The package allows integrating geonames database with a Laravel application.


## 🗒️ Description

The package is very useful for applications that rely on the geo data.

By default, the package provides 5 tables: `continents`, `countries`, `divisions`, `cities` and `translations` and fills them with data from the [geonames](https://www.geonames.org/) service.

It also allows to keep the data **up-to-date**, so the package fetches all daily modifications provided by the [geonames](https://www.geonames.org/) service and use them to synchronize your own database.

You can also set up package to seed only data that belongs to specific countries, disable unneeded tables, set up minimal population filter and use your own custom models.

Translations are powered by the [nevadskiy/laravel-translatable](https://github.com/nevadskiy/laravel-translatable).


## 🔌 Installation

```bash
composer require nevadskiy/laravel-geonames
```


## ✅ Requirements

- Laravel `7.0` or newer
- PHP `7.2` or newer


## 🔨 Usage

### Default structure and behaviour

#### Migrate the database

```bash
php artisan migrate
```

#### Run insert process

```bash
php artisan geonames:insert
```

It will insert download and insert the geonames dataset into your database.

#### Schedule updates

Add the following code to your console kernel (`app/Console/Kernel.php`) if you want to receive geonames daily updates.

```php
protected function schedule(Schedule $schedule)
{
    $schedule->command('geonames:update')->dailyAt('4:00');
}
```

> Note, that time is specified for the `UTC` timezone, so if you run server on another timezone, you need to convert time according to it. 

#### Insert custom structure

If you want to disable specific tables to be migrated, publish the package configuration

```
php artisan vendor:publish --tag=geonames-config
```

...to be continued


## 📑 Changelog

Please see [CHANGELOG](.github/CHANGELOG.md) for more information what has changed recently.


## ☕ Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for more information.


## 🔓 Security

If you discover any security related issues, please [e-mail me](mailto:nevadskiy@gmail.com) instead of using the issue tracker.


## 📜 License

The MIT License (MIT). Please see [LICENSE](LICENSE.md) for more information.
