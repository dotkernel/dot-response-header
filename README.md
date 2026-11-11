# dot-response-header

`dot-response-header` is Dotkernel's middleware for setting and overwriting custom response headers.

## Documentation

Documentation is available at: https://docs.dotkernel.org/dot-response-header/.

## Versions

| Version | Supported | PHP |
| --- | --- | --- |
| 4.x | :white_check_mark: | 8.3 to 8.6 |
| 3.x | Security fixes only | 8.2 to 8.5 |
| <= 2.x | :x: | |

Upgrading from 3.x? See [Upgrading to 4.x](https://docs.dotkernel.org/dot-response-header/v4/upgrading/).

## Badges

![OSS Lifecycle](https://img.shields.io/osslifecycle/dotkernel/dot-response-header)
![PHP from Packagist (specify version)](https://img.shields.io/packagist/php-v/dotkernel/dot-response-header/4.0.0)

[![GitHub issues](https://img.shields.io/github/issues/dotkernel/dot-response-header)](https://github.com/dotkernel/dot-response-header/issues)
[![GitHub forks](https://img.shields.io/github/forks/dotkernel/dot-response-header)](https://github.com/dotkernel/dot-response-header/network)
[![GitHub stars](https://img.shields.io/github/stars/dotkernel/dot-response-header)](https://github.com/dotkernel/dot-response-header/stargazers)
[![GitHub license](https://img.shields.io/github/license/dotkernel/dot-response-header)](https://github.com/dotkernel/dot-response-header/blob/4.0/LICENSE)

[![Build Static](https://github.com/dotkernel/dot-response-header/actions/workflows/continuous-integration.yml/badge.svg?branch=4.0)](https://github.com/dotkernel/dot-response-header/actions/workflows/continuous-integration.yml)
[![codecov](https://codecov.io/gh/dotkernel/dot-response-header/graph/badge.svg?token=NNRZN0FBF2)](https://codecov.io/gh/dotkernel/dot-response-header)
[![PHPStan](https://github.com/dotkernel/dot-response-header/actions/workflows/static-analysis.yml/badge.svg?branch=4.0)](https://github.com/dotkernel/dot-response-header/actions/workflows/static-analysis.yml)

## Requirements

- **PHP**: 8.3, 8.4, 8.5 or 8.6

## Installation

Run the following command in your project root directory:

```shell
composer require dotkernel/dot-response-header
```

Next, register the package's `ConfigProvider` to your application config.

```php
Dot\ResponseHeader\ConfigProvider::class,
```

> Make sure to register the package under the `// DK packages` section.

After registering the package, add it to the middleware stack in `config/pipeline.php` after `$app->pipe(RouteMiddleware::class);`

```php
$app->pipe(RouteMiddleware::class);
$app->pipe(\Dot\ResponseHeader\Middleware\ResponseHeaderMiddleware::class);
```

Create a new file `response-header.global.php` in `config/autoload` with the below configuration array:

```php
<?php

return [
    'dot_response_headers' => [
        '*'     => [
            'CustomHeader1' => [
                'value'     => 'CustomHeader1-Value',
                'overwrite' => true,
            ],
            'CustomHeader2' => [
                'value'     => 'CustomHeader2-Value',
                'overwrite' => false,
            ],
        ],
        'home'  => [
            'CustomHeader' => [
                'value' => 'header3',
            ],
        ],
        'login' => [
            'LoginHeader' => [
                'value'     => 'LoginHeader-Value',
                'overwrite' => false,
            ],
        ],
    ],
];
```

Because headers are matched with route names, we can have custom response headers for every request, by defining new headers under the `*` key.

All headers under `*` will be set for every response.

To add response headers for a specific route, define a new array using the exact route name as the array key.

Example:

```php
'dot_response_headers' => [
    'user' => [
        'UserCustomHeader' => [
            'value'     => 'UserCustomHeader-Value',
            'overwrite' => false,
        ],
    ],
],

// This will set a new header named UserCustomHeader with the UserCustomHeader-Value value for the route named 'user'
```

To overwrite an existing header use `overwrite => true`. The `*` headers are applied before the route headers, so a route header replaces a `*` header with the same name only when it sets `overwrite => true`.

An invalid configuration throws `Dot\ResponseHeader\Exception\InvalidConfigException` when the middleware is created, with a message naming the key.
