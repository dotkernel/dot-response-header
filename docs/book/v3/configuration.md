# Configuration

## Requirements

- **PHP**: 8.1, 8.2, 8.3 or 8.4

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

## Add configuration in autoload

Create a new file `response-header.global.php` in `config/autoload` with the below configuration array:

```php
<?php
return [
    'dot_response_headers' => [
        '*' => [
            'CustomHeader1' => [
                'value' => 'CustomHeader1-Value',
                'overwrite' => true,
            ],
            'CustomHeader2' => [
                'value' => 'CustomHeader2-Value',
                'overwrite' => false,
            ],
        ],
        'home' => [
            'CustomHeader' => [
                'value' => 'header3',
            ]
        ],
        'login' => [
            'LoginHeader' => [
                'value' => 'LoginHeader-Value',
                'overwrite' => false
            ]
        ],
    ]
]; 
```
