# Upgrading to 4.x

`dot-response-header` 4.x keeps the 3.x configuration format. A valid 3.x configuration sends the same headers in 4.x. Most applications only need to bump the version constraint.

```shell
composer require dotkernel/dot-response-header:^4.0
```

## Changes that can break your code

| Change | Symptom | Fix |
| --- | --- | --- |
| PHP 8.2 support dropped | Composer refuses to install | Upgrade to PHP 8.3 or newer |
| `overwrite` must be a `bool` | `InvalidConfigException: dot_response_headers.<route>.<header>.overwrite must be a bool, int given.` | Use `true` or `false`. In 3.x, `1` or `'true'` were silently treated as `false` |
| Route and header entries must be arrays | `InvalidConfigException` naming the key | In 3.x a string route entry was ignored with a PHP warning, and a string header entry threw a `TypeError` on every request |
| `value` must be a string, a number or a non-empty list of strings | `InvalidConfigException` naming the key | In 3.x `null` threw from the PSR-7 implementation on every request |
| The factory throws `Dot\ResponseHeader\Exception\InvalidConfigException` instead of `Exception` | Code that catches `Exception` still works; code that compares the exact class does not | Catch `InvalidArgumentException` or `InvalidConfigException` |
| `ConfigProvider` and `ResponseHeaderMiddlewareFactory` are `final` | `Class ... cannot extend final class` | Decorate them or write your own factory. `ResponseHeaderMiddleware` is not `final` |

## Changes in behaviour

| Change | Effect | What to do |
| --- | --- | --- |
| Configuration is checked when the middleware is created | Errors appear once, with the key in the message, instead of on each request | Nothing |
| `'dot_response_headers' => []` is allowed | In 3.x it threw `Unable to find dot-response-header config.`; now no headers are added | Nothing |
| A `config` service that implements `ArrayAccess` is accepted | In 3.x it threw a `TypeError` | Nothing |
| Numeric values are sent as strings | `3600` becomes `"3600"`, as before with Laminas Diactoros | Nothing |
| `mezzio/mezzio-router` 4.x is allowed | PHP 8.6 installs router 4.3 or newer | Nothing |
