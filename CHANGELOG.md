# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## 0.5.0 - 2023-02-24

### Added

- Support Laravel 10

## [Unreleased]

### Added

## [0.4.3] - 2022-11-27

### Changes

- Upgrade `nevadskiy/downloader` package

## [0.4.2] - 2022-08-09

### Changes

- Add possibility to override dependent seeders

## [0.4.1] - 2022-08-09

### Changes

- Seeder stubs

## [0.4.0] - 2022-08-09

### Added

- Independent seeders
- Laravel 9 support
- `nevadskiy/downloader` library
- `league/csv` library

### Changed

- Simplified API
- Syncing strategy
- Fix migration fields
- Move models to stubs
- Refactoring

### Removed

- Unused code
- Old suppliers
- Direct translations dependency
- UUID dependency
- Parser classes
- `FileReader` class
- `Downloader` class

## [0.3.0] - 2022-04-29

### Changed

- Rename `ReadOnly` trait to avoid conflict on PHP 8.1

## [0.2.3] - 2020-11-29

### Changed

- Update uuid package

## [0.2.2] - 2020-07-15

### Added

- Add json serialize to location value object

## [0.2.1] - 2020-05-19

### Added

- Support PHP 8

### Changed

- Nova resources now is read only by default

## [0.2.0] - 2020-03-28

### Added

- Possibility to define custom models
- Basic tests with fixtures
- Add insert process logging
- Index in `cities` table to `feature_code` column

### Changed

- Nova resources now is read only by default

### Fixed

- Code style

## [0.1.3] - 2020-03-19

### Fixed

- Update geonames command

## [0.1.2] - 2020-03-19

### Added

- Cities relation to the Country model

## [0.1.1] - 2020-03-19

### Added

- Database indexes

### Fixed

- Fix booting nova resources without nova dependency

## [0.1.0] - 2020-03-18

### Added

- Everything
