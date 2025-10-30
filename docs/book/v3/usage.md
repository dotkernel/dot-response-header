# Usage

Because headers are matched with route names, we can have custom response headers for every request, by defining new headers under the `*` key, in the configuration file `response-header.global.php`.

All headers under `*` will be set for every response.

To add response headers for a specific set of routes, define a new array using the route name as the array key.

## Example

```php
'dot_response_headers' => [
    'user' => [
        'UserCustomHeader' => [
            'value'     => 'UserCustomHeader-Value',
            'overwrite' => false,
        ],
    ],
],

// This will set a new header named UserCustomHeader with the UserCustomHeader-Value value for any route name matching 'user'
```

To overwrite an existing header use `overwrite => true`.
