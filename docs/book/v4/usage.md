# Usage

Because headers are matched with route names, we can have custom response headers for every request, by defining new headers under the `*` key, in the configuration file `response-header.global.php`.

All headers under `*` will be set for every response, including 404 and 405 responses.

To add response headers for a specific route, define a new array using the route name as the array key. The key must be the exact route name; it is not a pattern.

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

// This will set a new header named UserCustomHeader with the UserCustomHeader-Value value for the route named 'user'
```

## Overwriting headers

Headers are applied in this order:

1. The response returned by the handler.
2. The `*` headers.
3. The headers of the matched route.

A header that already exists is only replaced when its entry has `'overwrite' => true`. This also applies between steps 2 and 3: a route header replaces a `*` header with the same name only when it sets `'overwrite' => true`.

```php
'dot_response_headers' => [
    '*'     => [
        'Cache-Control' => [
            'value' => 'no-store',
        ],
    ],
    'image' => [
        'Cache-Control' => [
            'value'     => 'public, max-age=86400',
            'overwrite' => true,
        ],
    ],
],

// The 'image' route gets "Cache-Control: public, max-age=86400", every other route gets "Cache-Control: no-store"
```

Header names are case-insensitive, so `x-custom` set by a handler counts as an existing `X-Custom` header.

## Header values

A value can be a string, a number or a list of strings:

```php
'dot_response_headers' => [
    '*' => [
        'Access-Control-Max-Age' => [
            'value' => 3600,
        ],
        'Vary' => [
            'value' => ['Accept', 'Origin'],
        ],
    ],
],
```
