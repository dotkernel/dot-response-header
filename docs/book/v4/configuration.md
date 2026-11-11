# Configuration

## Requirements

- **PHP**: 8.3, 8.4, 8.5 or 8.6
- **mezzio/mezzio-router**: 3.x or 4.x (PHP 8.6 needs 4.3 or newer)

## Register ConfigProvider

Next, register the package's `ConfigProvider` to your application config.

```php
Dot\ResponseHeader\ConfigProvider::class,
```

> Make sure to register the package under the `// DK packages` section.

## Add the package to the middleware stack

After registering the package, add it to the middleware stack in `config/pipeline.php` after `$app->pipe(RouteMiddleware::class);`

```php
$app->pipe(RouteMiddleware::class);
$app->pipe(\Dot\ResponseHeader\Middleware\ResponseHeaderMiddleware::class);
```

> If the middleware is piped before `RouteMiddleware`, no route is known yet and only the `*` headers are added.

## Add configuration in autoload

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

## Configuration keys

| Key | Type | Default | Meaning |
| --- | --- | --- | --- |
| `dot_response_headers` | array | required | Route name (or `*`) to headers. An empty array is allowed and adds no headers |
| `<route>.<header>.value` | string, int, float or a non-empty list of strings | none | Header value. Numbers are sent as strings; a list sends one header with several values. An entry without `value` is skipped |
| `<route>.<header>.overwrite` | bool | `false` | Replace the header if the response already has it |

## Invalid configuration

The configuration is checked once, when the container creates the middleware. A wrong type throws `Dot\ResponseHeader\Exception\InvalidConfigException` with a message naming the key, for example:

```text
dot_response_headers.*.X-Frame-Options.overwrite must be a bool, int given.
```

A missing `config` service or a missing `dot_response_headers` key also throws `InvalidConfigException`.
