# 🌎 Laravel Geonames

**Currently, the work in progress. It will receive updates with possible breaking changes. Not recommended using in production environments yet.**

The package allows integrating geonames database with a Laravel application.


## 🗒️ Description

The package is very useful for applications that rely on the geo data.

By default, the package provides 5 tables: `continents`, `countries`, `divisions`, `cities` and `translations` and fills them with data from the [geonames](https://www.geonames.org/) service.

It also allows to keep the data **up-to-date**, so the package fetches all daily modifications provided by the [geonames](https://www.geonames.org/) service and use them to synchronize your own database.

You can also set up the package to seed only data that belongs to specific countries, disable unneeded tables, set up minimal population filter and use your own custom models.

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

- Migrate the database

```bash
php artisan migrate
```

- Run insert process

```bash
php artisan geonames:insert
```

It will insert download and insert the geonames dataset into your database.

> Note that the insert process may take some time. On average, it is about 15 minutes (without downloading time). 

### Schedule updates

Add the following code to your console kernel (`app/Console/Kernel.php`) if you want to receive geonames daily updates.

```php
protected function schedule(Schedule $schedule)
{
    $schedule->command('geonames:update')->dailyAt('4:00');
}
```

> Note, that time is specified for the `UTC` timezone, so if you run server on another timezone, you need to convert time according to it. 

### Configure custom structure

If you want to configure package according to your needs, you need to publish the package configuration first.

```
php artisan vendor:publish --tag=geonames-config
```

#### Specifying source

You can choose appropriate data source for seeding as one of `SOURCE_ALL_COUNTRIES`, `SOURCE_SINGLE_COUNTRY` or `SOURCE_ONLY_CITIES`.

The default is `SOURCE_ALL_COUNTRIES` that indicates to fetch data from the [allCountries.zip](http://download.geonames.org/export/dump/) file.
It contains all 4 models (`continents`, `countries`, `divisions` and `cities`). 
You can configure [filters](#specifying-filters)  in the `geonames` configuration file to specify [countries](#countries-filter) that is going to be seeded and [minimal population](#population-filter).

The `SOURCE_SINGLE_COUNTRY` source is used to fetch data from country-based files (e.g. [US.zip](http://download.geonames.org/export/dump/)).
The `continents` table will not be seeded with this source.
You can specify which country (or countries) you are going to seed specifying [countries filter](#countries-filter) in the `geonames` configuration file.

The `SOURCE_ONLY_CITIES` source is used to fetch data from city-based files (e.g. [cities500.zip](http://download.geonames.org/export/dump/)).
There is only `cities` table available with this source type.
You can specify [minimal population filter](#population-filter) to indicate which file it is going to fetch by population.
It is also possible to use countries filter to seed cities that belongs only to specific countries.

#### Specifying filters

By default, there are two filters which you can use to filter data being seeded.

#### Countries filter

The `countries` filter is used to filter data that belongs only to specific country (or countries).
If the `SOURCE_SINGLE_COUNTRY` [source](#specifying-source) is specified, this filter will be used to download a country-based data source.
The default is `*` that indicates that all countries are allowed. Multiple countries can be specified as an array of ISO country codes.

Example:
```php
'filters' => [
    'countries' => ['AU', 'US']
]
```

#### Population filter

The `population` filter is used to filter cities by the indicated minimal population.
If the `SOURCE_ONLY_CITIES` [source](#specifying-source) is specified, this filter will be used to download a city-based data source.
Any value from `0` and higher has be used, but in combination with the `SOURCE_ONLY_CITIES` source, available values are only `500`, `1000`, `5000` and `15000`.

Example:

```php
'filters' => [
    'population' => 15000
]
```

#### Overriding models

Most likely you will need your own models with their own behaviour and relations instead of provided ones by default.

To override them, use `models` key in the `geonames` configuration file.

If you are not going to use any model, you can switch the value to `false` and then corresponding tables will not be migrated at all.

For example, most applications probably will not need continent model.

```php
'models' => [
    'continent' => false,
    'country' => App\Models\Country::class,
    'division' => App\Models\Division::class,
    'city' => App\Models\City::class,
],
```

#### Overriding migrations

To rename tables or remove unnecessary fields, you can publish migrations and edit them before execution migrations command.

- To publish default migrations, use the following command:

```
php artisan vendor:publish --tag=geonames-migrations
```

- Then disable default migrations from being migrated, setting `default_migrations` to `false` in the `geonames` configuration file.

```php
'default_migrations' => false
```

#### Inserting custom structure

- After configuring [source](#specifying-source) and [filters](#specifying-filters), you can execute migrations command.
It will determine which tables should be migrated and create them in the database.

```bash
php artisan migrate
```

- Then you can run insert command.

```bash
php artisan geonames:insert
```

#### Reinserting dataset

Sometimes you may need to delete all data and reinsert it again. To do it, pass the `--reset` option to `geonames:insert` command.

```bash
php artisan geonames:insert --reset
```

## 📑 Changelog

Please see [CHANGELOG](.github/CHANGELOG.md) for more information what has changed recently.


## ☕ Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for more information.


## 🔓 Security

If you discover any security related issues, please [e-mail me](mailto:nevadskiy@gmail.com) instead of using the issue tracker.


## 📜 License

The MIT License (MIT). Please see [LICENSE](LICENSE.md) for more information.

## 🔨 To Do

- [ ] add addressing feature:
  - https://twitter.com/danjharrin/status/1528682224916254720
  - https://github.com/commerceguys/addressing
- [ ] consider adding soft deletes.
- [ ] seed only columns from mapping that are present in the database table
- [ ] add report for daily update / delete
- [ ] update cs fixer
- [ ] add possibility to use source with multiple countries
- [ ] consider scopes for deletes what if records is using in the app?
- [ ] probably add global method `withoutDeletes` to disable deleting at all (and in the sync method as well).
- [ ] refactor filters for cities population or country only
- [ ] add minimum laravel version with upserts (or throw an exception if version is lower)
- [ ] add info about 256 MB memory required for seeding (add possibility to configure chunks)
- [ ] add info about SYNC command (it can be used when parameters changed to sync according to new seeder configuration, for example, when new country added or population increased, etc)
- [ ] add report for sync command (created: 3, updated: 12, deleted: 1)
- [ ] probably return lazy collection from parser
- [ ] add possibility to seed default structure (using same tables and models) as a separate strategy
- [ ] provide basic kit for local seeding and testing
- [ ] feature model deleting (cities, divisions, countries, continents)
- [ ] add GeonamesServiceProvider to publish and register there models and other set up
- [ ] think about timestamps for syncing and daily updates
- [ ] use minimal set up (no morph map, no nova resources, no uuid)
- [ ] add doc how to avoid memory leaks (ignition, telescope, etc.), remove `GeonamesReadyEvent`
  - [ ] for flare disable query report: report_query_bindings 
- [ ] add possibility to store country capitals separately (probably use PCL code for capitals from cities table)
- [ ] specify correct translations version
- [ ] move nova resources into stubs
- [ ] add possibility to customize models (probably also use them as stubs)
- [ ] remove UUID
- [ ] check `no-country.zip` geonames file

- [ ] PR to laravel method that creates a lazy collection instance from iterator/generator
