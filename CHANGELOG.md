# Changelog

## 4.0.0 - TBD

### Changed

- PHP requirement is now `~8.3.0 || ~8.4.0 || ~8.5.0 || ~8.6.0`
- `mezzio/mezzio-router` requirement is now `^3.16 || ^4.0`
- The header configuration is validated when the middleware is created; a wrong type throws `Dot\ResponseHeader\Exception\InvalidConfigException` naming the key
- `overwrite` must be a `bool`; `value` must be a string, a number or a non-empty list of strings
- `ResponseHeaderMiddlewareFactory` throws `InvalidConfigException` instead of `Exception` (the messages are unchanged)
- `ConfigProvider` and `ResponseHeaderMiddlewareFactory` are now `final`
- PHPUnit `^12.5`, PHPStan level 8, PHPUnit fails on deprecations, notices, warnings and risky tests
- Tests use real PSR-7 objects and fixtures instead of mocks
- CI: GitHub Actions bumped to `actions/checkout@v7`, `actions/cache@v6`, `codecov/codecov-action@v7`; coverage runs on PHP 8.5 only

### Added

- Support for PHP 8.6
- Support for `mezzio/mezzio-router` 4
- `Dot\ResponseHeader\Exception\InvalidConfigException`
- Explicit `psr/container`, `psr/http-server-handler` and `psr/http-server-middleware` requirements
- `v4` documentation and the "Upgrading to 4.x" guide

### Deprecated

- Nothing

### Removed

- Support for PHP 8.2
- `psr/http-client` requirement (it was never used)

### Fixed

- An empty `dot_response_headers` array no longer throws in the factory
- A `config` service that is not an array no longer throws a `TypeError`
- Invalid header entries no longer raise PHP warnings or errors on every request

## 3.6.0 - 2025-10-30

### Changed

- Nothing

### Added

- Support for PHP 8.4 and 8.5

### Deprecated

- Nothing

### Removed

- Support for PHP 8.1

### Fixed

- Nothing

## 3.5.0 - 2025-03-12

### Changed

- Replaced Psalm with PHPStan

### Added

- Nothing

### Deprecated

- Nothing

### Removed

- `vimeo/psalm`

### Fixed

- Nothing

## 3.4.1 - 2025-01-28

### Changed

- Bumped `vimeo/psalm` to `^6.0`
- "DotKernel" spelled "Dotkernel" in `composer.json`

### Added

- Nothing

### Deprecated

- Nothing

### Removed

- Nothing

### Fixed

- Nothing

## 3.4.0 - 2024-10-22

### Changed

- Bumped `laminas/laminas-coding-standard` to `^3.0`

### Added

- Support for PHP 8.4

### Deprecated

- Nothing

### Removed

- Nothing

### Fixed

- Nothing

## 3.3.1 - 2024-05-27

### Changed

- `psr/http-message` requirement back to `^1.0 || ^2.0`

### Added

- Nothing

### Deprecated

- Nothing

### Removed

- Nothing

### Fixed

- Nothing

## 3.3.0 - 2024-05-03

### Changed

- `psr/http-message` requirement is now `^2.0`

### Added

- Documentation for v3
- Unsupported versions in `SECURITY.md`

### Deprecated

- Nothing

### Removed

- Nothing

### Fixed

- `mkdocs.yml` navigation and README examples

## 3.2.3 - 2023-11-30

### Changed

- Nothing

### Added

- Support for PHP 8.3

### Deprecated

- Nothing

### Removed

- Nothing

### Fixed

- Nothing

## 3.2.2 - 2023-08-22

### Changed

- README badges

### Added

- Codecov workflow and badge

### Deprecated

- Nothing

### Removed

- Nothing

### Fixed

- Nothing

## 3.2.1 - 2023-08-16

### Changed

- `psr/http-message` requirement is now `^1.0 || ^2.0`
- `laminas/laminas-diactoros` dev requirement is now `^3.1`

### Added

- Nothing

### Deprecated

- Nothing

### Removed

- Nothing

### Fixed

- Nothing

## 3.2.0 - 2023-07-28

### Changed

- PHP requirement is now `~8.1.0 || ~8.2.0`
- Directory structure and namespaces
- PHPUnit `^10.2`

### Added

- Support for PHP 8.2
- Explicit `mezzio/mezzio-router`, `psr/http-client` and `psr/http-message` requirements
- Unit tests, `laminas/laminas-coding-standard` and Psalm static analysis
- `composer check` script

### Deprecated

- Nothing

### Removed

- Support for PHP 7.4 and 8.0

### Fixed

- Nothing

## 3.1.0 - 2022-04-06

### Changed

- PHP requirement is now `~7.4.0 || ~8.0.0 || ~8.1.0`
- PHPUnit `^9.5.20`

### Added

- Support for PHP 8.0 and 8.1

### Deprecated

- Nothing

### Removed

- Nothing

### Fixed

- Nothing

## 3.0.1 - 2021-05-18

### Changed

- README.md
- src/Middleware/ResponseHeaderMiddleware.php

### Added

- CHANGELOG.md

### Deprecated

- Nothing

### Removed

- Nothing

### Fixed

- Nothing

## 3.0.0 - 2021-05-14

### Changed

- Nothing

### Added

- Everything

### Deprecated

- Nothing

### Removed

- Nothing

### Fixed

- Nothing
