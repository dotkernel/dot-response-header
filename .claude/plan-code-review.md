# Plan: dotkernel/dot-response-header 4.0.0

Plan for the next major release of `dotkernel/dot-response-header`, written on 2026-11-11 on the `upgrade` branch (cut from `3.0` at tag `3.6.0`). The target is `4.0.0`, supporting PHP 8.3 to 8.6, PHPUnit 12, PHPStan level 8, and `mezzio/mezzio-router` 3.x and 4.x. The work is on `upgrade` and will be merged into a new `4.0` branch after review. Re-checked on 2026-11-11 after the maintainer's `composer.json` edits and `composer update`, then implemented the same day (rollout steps 2 to 8). `composer check` passes. Nothing is committed.

## Implementation status

| Item | State | Notes |
| --- | --- | --- |
| V1 PHP range in `composer.json` | Done by the maintainer | `~8.3.0 \|\| ~8.4.0 \|\| ~8.5.0 \|\| ~8.6.0`; `composer why-not php 8.6.0` and `8.3.0` find no blocker |
| V2 `phpunit/phpunit` to 12 | Done by the maintainer | `^12.5`; 12.5.43 installed |
| V3 Remaining `composer.json` edits | Done by the maintainer | Exactly as planned; `composer validate --strict` passes. Optional: router floor `^3.17` (see V3) |
| V4 Workflows and `.laminas-ci.json` | Done | Maintainer: versions and actions. Me: `codecov.yml` on 8.5 only; `static-analysis.yml` job id `static-analysis`, `coverage: none`. All 4 workflow files parse as YAML; not run in CI yet. `.laminas-ci.json` unchanged |
| V5 `phpstan.neon` level 8 | Done | Level 8 and `--level max` both clean in the repository, no ignores |
| V6 `phpunit.xml` and `.gitignore` | Done | Local schema, `failOn*` flags (confirmed: the 5 old notices made PHPUnit exit 1). `.gitignore` has `.phpunit.cache` and keeps `.phpunit.result.cache` for 3.x checkouts |
| F1 to F6 code fixes | Done | Code as in the findings; `src/Exception/InvalidConfigException.php` added |
| F7 to F9 tests | Done | 27 tests, 42 assertions; fixtures `ArrayContainer`, `ServiceNotFoundException`, `FixedResponseHandler`. 14 failed against the 3.6.0 `src/` before step 5 (7 errors, 7 failures). No mocks left |
| D1 docs `v4`, README, SECURITY, mkdocs | Done | New `docs/book/v4/` (overview, installation, configuration, usage, upgrading). All 10 nav targets exist; `mkdocs build` not run (not installed). Badges and the license link in README and `v4/overview.md` point at branch `4.0`; `v3/overview.md` keeps `3.0` |
| D2 CHANGELOG | Done | `3.0.0` to `4.0.0`, newest first, `3.0.1` date fixed to 2021-05-18; lint-clean |
| D3 Upgrade guide | Done | `docs/book/v4/upgrading.md`; the error message it quotes was produced at runtime |
| Release | Waiting for the maintainer | Review, merge `upgrade` into `4.0`, tag `4.0.0`, CHANGELOG date, re-enable the BC check after release |

## Decision: version 4

- `4.0.0` is a major release because it drops PHP 8.2, and the recommended fixes (F2, F3, F5) change behaviour for misconfigured applications and make two classes `final`.
- Release lines in the repository: branches `3.0` (default), `upgrade` (this work) and `gh-pages`; tags `3.0.0` to `3.6.0`. There is no `4.x` branch and no parallel line, so 4.x is the direct successor of 3.x. No discontinued-line page is needed (T13 does not apply).
- The work is on `upgrade` and will be merged into `4.0` after review (decided by the maintainer on 2026-11-11). Badges and links that name a branch (`?branch=`, `blob/<branch>/LICENSE`) in README and `v4/overview.md` use `4.0`; `v3/overview.md` keeps `3.0`. `3.0` stays for security fixes.
- After release, `3.0` should be marked "security fixes only" in `SECURITY.md` and the README.

## Baseline

Run on 2026-11-11, first in scratch (before `vendor/` existed), then re-run in the repository after the maintainer's `composer update`.

| Check | Result |
| --- | --- |
| `git describe --tags` | `3.6.0` (HEAD is exactly the tag) |
| PHP available locally | 8.5.12 only. No pcov or Xdebug |
| Installed in the repository | PHPUnit 12.5.43, PHPStan 2.3.5, phpstan-phpunit 2.1.1, mezzio-router 4.3.0, diactoros 3.10.0, laminas-coding-standard 3.1.0, psr/container 2.0.2, psr/http-message 2.0, psr/http-server-handler 1.0.2, psr/http-server-middleware 1.0.2 |
| `composer validate --strict` | Valid |
| `composer check` in the repository | **Fails at PHPStan**: phpcs clean; PHPUnit 9 tests, 45 assertions, OK with 5 PHPUnit notices; PHPStan level 8 the same 7 errors as in V5. Identical to scratch A |
| `composer why-not php 8.6.0` / `8.3.0` | No installed package blocks either version |
| `composer show -l --direct` | All direct dependencies at their latest allowed version. PHPUnit 13.4.6 exists (requires PHP `>=8.4.1`), so 12 stays the right major while 8.3 is supported |
| Scratch runs (before `vendor/` existed) | Against two sibling `vendor/` directories (read-only symlinks) with a bootstrap that adds this package's PSR-4 namespaces; see below |
| Scratch A: `dot-errorhandler/vendor` | PHPUnit 12.5.43, PHPStan 2.3.5, phpstan-phpunit 2.1.1, mezzio-router **4.3.0**, diactoros 3.10.0, laminas-coding-standard 3.1.0 |
| Scratch B: `admin/vendor` | PHPUnit 12.5.38, PHPStan 2.2.17, mezzio-router **3.20.0**, diactoros 3.8.0, mezzio 3.x with FastRoute and laminas-servicemanager |
| phpcs | Clean (A) |
| PHPUnit 12, current tests | 9 tests, 45 assertions, OK **with 5 PHPUnit notices** (A and B): mock objects without expectations |
| PHPStan level 5 (old level) | Clean (B) |
| PHPStan level 8 | 7 errors (A and B), listed in V5 |
| Laminas BC check | Disabled by the maintainer (`backwardCompatibilityCheck: false`). Enabled, it would compare against `3.6.0` and report the intended breaks (F5 `final`, F2 validation). Re-enable after `4.0.0` is tagged so 4.x minors are checked against `4.0.0` |

## Done by the maintainer

Uncommitted edits found in the working tree:

| File | Edit | Status |
| --- | --- | --- |
| `composer.json` | `php`: first `~8.3.0 \|\| ~8.4.0 \|\| ~8.5.0 \|\| ~8.2.0`, corrected to `~8.3.0 \|\| ~8.4.0 \|\| ~8.5.0 \|\| ~8.6.0` | Correct after the re-check |
| `composer.json` | `phpunit/phpunit`: `^10.2` to `^12.5` | Correct |
| `composer.json` | V3 edits: router `^3.16 \|\| ^4.0`, `psr/container`, `psr/http-server-handler`, `psr/http-server-middleware` added, `psr/http-client` removed, diactoros `^3.10` | Correct, matches the diff below |
| `phpstan.neon` | `level: 5` to `level: 8` | Correct |
| `codecov.yml`, `static-analysis.yml` | PHP matrix 8.3 to 8.6; `actions/checkout@v7`, `actions/cache@v6`, `codecov/codecov-action@v7` | Correct, see V4 for the rest |
| `.laminas-ci.json` | `ignore_php_platform_requirements` for 8.6; `backwardCompatibilityCheck: false` | Correct for the major |

The `composer.json` edits that were requested (all applied by the maintainer and installed):

```diff
   "require": {
-    "php": "~8.3.0 || ~8.4.0 || ~8.5.0 || ~8.2.0",
-    "mezzio/mezzio-router": "^3.16",
-    "psr/http-client": "^1.0",
-    "psr/http-message": "^1.0 || ^2.0"
+    "php": "~8.3.0 || ~8.4.0 || ~8.5.0 || ~8.6.0",
+    "mezzio/mezzio-router": "^3.16 || ^4.0",
+    "psr/container": "^1.1 || ^2.0",
+    "psr/http-message": "^1.0 || ^2.0",
+    "psr/http-server-handler": "^1.0",
+    "psr/http-server-middleware": "^1.0"
   },
   "require-dev": {
     "laminas/laminas-coding-standard": "^3.0",
-    "laminas/laminas-diactoros": "^3.1",
+    "laminas/laminas-diactoros": "^3.10",
     "phpstan/phpstan": "^2.1",
     "phpstan/phpstan-phpunit": "^2.0",
     "phpunit/phpunit": "^12.5"
   },
```

Optional follow-up (see V3): `"mezzio/mezzio-router": "^3.17 || ^4.0"`, because no `3.16.x` release can install on PHP 8.3 or newer.

## Findings

### V1. PHP range

- Status: fixed by the maintainer on 2026-11-11; the "After" below is now in `composer.json`.
- Location: `composer.json:22`.
- Evidence (before the fix): the constraint read `~8.3.0 || ~8.4.0 || ~8.5.0 || ~8.2.0`. 8.2 is still allowed and 8.6 is missing. PHPUnit 12 requires `php >=8.3` (read from `vendor/phpunit/phpunit/composer.json`), so a PHP 8.2 install cannot resolve dev dependencies. The custom workflows run 8.6, where `composer install` refuses the root package. Laminas CI builds its matrix from this constraint, so it never runs 8.6.

Before:

```json
"php": "~8.3.0 || ~8.4.0 || ~8.5.0 || ~8.2.0",
```

After:

```json
"php": "~8.3.0 || ~8.4.0 || ~8.5.0 || ~8.6.0",
```

- Why: matches the agreed range (8.3 to 8.6), the workflows and PHPUnit 12.

### V2. PHPUnit 12 (T5)

- Done by the maintainer in `composer.json`. What the tests and config need:
    - `phpunit.xml` schema points at 10.2 (V6).
    - Annotations: none are used, so nothing to convert. New data providers must be `public static` and attached with `#[DataProvider]` (F8 does this).
    - Mock rules: PHPUnit 12 raises a notice for a mock with no expectations. The current `ResponseHeaderMiddlewareTest::setUp()` creates two mocks for every test, which gives the 5 notices in the baseline. F8 replaces them with real objects and a fixture handler; `createStub()` would be the fallback.
    - `PHPUnit\Framework\MockObject\Exception` in `@throws` tags disappears with the mocks.
    - `failOn*` flags: V6.

### V3. Undeclared and unused dependencies (T10, T11)

Imports in `src/` (from `grep -rn "^use " src`) against `require`:

| Package | Used in `src/` | Declared | Action |
| --- | --- | --- | --- |
| `mezzio/mezzio-router` | `RouteResult` (middleware) | `^3.16` | Widen to `^3.16 \|\| ^4.0` |
| `psr/container` | Factory | No (comes via `mezzio-router`) | Add `^1.1 \|\| ^2.0` |
| `psr/http-message` | Middleware | `^1.0 \|\| ^2.0` | Keep |
| `psr/http-server-middleware` | `MiddlewareInterface` | No | Add `^1.0` |
| `psr/http-server-handler` | `RequestHandlerInterface` | No | Add `^1.0` |
| `psr/http-client` | Nowhere in `src/` or `test/` | `^1.0` | Remove |

- `mezzio-router` 4.x: `4.3.0` (read from `dot-errorhandler/vendor`) declares `php: ~8.3.0 || ~8.4.0 || ~8.5.0 || ~8.6.0`; `3.20.0` (from `admin/vendor`) declares `~8.2.0 || ... || ~8.5.0`. So **PHP 8.6 needs router 4.x**. `RouteResult::getMatchedRouteName(): false|string` and `isSuccess()` are the same in both. The full suite and PHPStan pass against both (Baseline A and B). `mezzio/mezzio` 3.29.0 accepts `^3.15.0 || ^4.0.0`.
- Lowest installable router per PHP version, from Packagist metadata (`composer show --all mezzio/mezzio-router <version>`):

| PHP | Lowest router allowed by `^3.16 \|\| ^4.0` | Why |
| --- | --- | --- |
| 8.3 | 3.17.0 | 3.16.x declares `~8.1.0 \|\| ~8.2.0` only |
| 8.4 | 3.18.0 | |
| 8.5 | 3.19.0 | |
| 8.6 | 4.3.0 | No 3.x release and no 4.0 to 4.2 release declares 8.6 (4.0.x stops at 8.4, 4.1 and 4.2 at 8.5) |

- So the `3.16` floor is unreachable on every supported PHP version. It does no harm (Composer filters by platform), but `^3.17 || ^4.0` states the real floor. Optional; not needed for correctness.
- Removal evaluation for `mezzio-router` (T11): it is needed for one class, `RouteResult`, which is how the middleware finds the matched route name. There is no PSR interface for a matched route. Options: (a) keep it, (b) read a configurable request attribute instead, which breaks every existing config and loses the type check. Recommendation: **keep it**; the package is Mezzio middleware by design.
- `laminas/laminas-diactoros` (dev): `3.10.0` declares 8.6; `3.8.0` and `3.9.0` stop at 8.5. Raise to `^3.10` so lowest-dependency CI on 8.6 does not need the platform override for it.
- `suggest` and `conflict`: none needed.
- `laminas/laminas-servicemanager` is not a runtime requirement, so T20 does not apply.

### V4. Workflows (T4)

Checked every file in `.github/workflows` and `.laminas-ci.json`.

| File | State | Change |
| --- | --- | --- |
| `continuous-integration.yml` | Uses `laminas/workflow-continuous-integration@1.x` | None. Its matrix follows the `composer.json` PHP range (V1) |
| `docs-build.yml` | Uses `dotkernel/documentation-theme/...@main` | None |
| `codecov.yml` | Bumped by the maintainer | Run on one PHP version only |
| `static-analysis.yml` | Bumped by the maintainer | `coverage: none`; rename job id `mutation` |
| `.laminas-ci.json` | Edited by the maintainer | None now; re-enable BC check after `4.0.0` |

`codecov.yml` uploads four coverage reports per push, and pcov for PHP 8.6 may not exist yet (unverified).

Before (`codecov.yml`):

```yaml
        php:
          - "8.3"
          - "8.4"
          - "8.5"
          - "8.6"
```

After:

```yaml
        php:
          - "8.5"
```

`static-analysis.yml` loads pcov although PHPStan never uses it, and the job id says `mutation`.

Before:

```yaml
jobs:
  mutation:
    name: PHPStan ${{ matrix.php }}-${{ matrix.os }}
...
          coverage: pcov
```

After:

```yaml
jobs:
  static-analysis:
    name: PHPStan ${{ matrix.php }}-${{ matrix.os }}
...
          coverage: none
```

- Why: the 8.6 row in both custom workflows can install now that V1 is in. `composer why-not php 8.6.0` finds no blocker in the installed (latest) tree, so no `--ignore-platform-req` is needed for latest dependencies.
- `.laminas-ci.json` `ignore_php_platform_requirements` for 8.6: likely no longer needed for direct dependencies (router 4.3.0 and diactoros 3.10.0 both declare 8.6). Keep it until a CI run on lowest dependencies for 8.6 is green without it, then remove it.

### V5. PHPStan level 8 (T3)

Current level was 5 (clean). At level 8, identical on router 3.20 and 4.3:

| # | Location | Identifier | Fix |
| --- | --- | --- | --- |
| 1 | `src/ConfigProvider.php:12` | `missingType.iterableValue` | Array shape `@return` (F6) |
| 2 | `src/ConfigProvider.php:19` | `missingType.iterableValue` | Array shape `@return` (F6) |
| 3 | `src/Middleware/ResponseHeaderMiddleware.php:19` | `missingType.iterableValue` | Typed config property (F2) |
| 4 | `src/Middleware/ResponseHeaderMiddleware.php:21` | `missingType.iterableValue` | `@param array<mixed>`, normalised into the typed property (F2) |
| 5 | `src/Middleware/ResponseHeaderMiddleware.php:36` | `argument.type` (`string\|false` given) | Check `getMatchedRouteName() !== false` (F1) |
| 6 | `test/Middleware/ResponseHeaderMiddlewareTest.php:34` | `argument.type` (`MockObject\|ServerRequestInterface`) | Real `ServerRequest`, no mocks (F8) |
| 7 | `test/Middleware/ResponseHeaderMiddlewareTest.php:34` | `argument.type` (`MockObject\|RequestHandlerInterface`) | Fixture handler (F8) |

No ignores, baseline, `assert()` or `@var` overrides are used. The prototype is clean at level 8 and at `max`.

### V6. `phpunit.xml` and `.gitignore` (T5, T15)

Before:

```xml
<phpunit xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="https://schema.phpunit.de/10.2/phpunit.xsd" bootstrap="./vendor/autoload.php" colors="true">
    <testsuites>
        <testsuite name="dot-response-header Test Suite">
            <directory>./test</directory>
        </testsuite>
    </testsuites>
    <coverage/>
    <source>
        <include>
            <directory suffix=".php">./src</directory>
        </include>
    </source>
</phpunit>
```

After (validated by PHPUnit 12.5.38 and 12.5.43 in scratch):

```xml
<?xml version="1.0" encoding="UTF-8"?>
<phpunit xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
         xsi:noNamespaceSchemaLocation="vendor/phpunit/phpunit/phpunit.xsd"
         bootstrap="vendor/autoload.php"
         cacheDirectory=".phpunit.cache"
         colors="true"
         failOnDeprecation="true"
         failOnNotice="true"
         failOnWarning="true"
         failOnRisky="true"
         failOnPhpunitDeprecation="true"
         failOnPhpunitNotice="true"
         displayDetailsOnTestsThatTriggerDeprecations="true"
         displayDetailsOnTestsThatTriggerNotices="true"
         displayDetailsOnTestsThatTriggerWarnings="true"
         displayDetailsOnPhpunitDeprecations="true"
         displayDetailsOnPhpunitNotices="true">
    <testsuites>
        <testsuite name="dot-response-header">
            <directory>test</directory>
        </testsuite>
    </testsuites>
    <source ignoreSuppressionOfDeprecations="true">
        <include>
            <directory>src</directory>
        </include>
    </source>
</phpunit>
```

`.gitignore`: add `.phpunit.cache`; keep `.phpunit.result.cache`, which PHPUnit still writes on a 3.x checkout.

- Why: the local schema follows the installed PHPUnit; the flags turn the 5 current notices (and any future deprecation) into failures. Deprecations raised inside vendor code on PHP 8.6 would also fail the build; if that happens, the fix is a dependency bump, or `<source>` already limits deprecation reporting to `src`, so first-party code stays strict (unverified on 8.6).

### F1. Matched route name is `false|string`

- Severity: low (type safety; no runtime failure found).
- Location: `src/Middleware/ResponseHeaderMiddleware.php:35-36`.
- Evidence: PHPStan error V5 #5. `isSuccess()` makes it safe today, but the type is not narrowed.

Before:

```php
$routeResult = $request->getAttribute(RouteResult::class);
if ($routeResult instanceof RouteResult && $routeResult->isSuccess()) {
    $response = $this->addHeaders($response, $routeResult->getMatchedRouteName());
}
return $response;
```

After:

```php
$routeResult = $request->getAttribute(RouteResult::class);
if (! $routeResult instanceof RouteResult) {
    return $response;
}

$routeName = $routeResult->getMatchedRouteName();
if ($routeName === false) {
    return $response;
}

return $this->addHeaders($response, $routeName);
```

- Why: `getMatchedRouteName()` returns `false` exactly when the result is a failure in both router 3.20 and 4.3, so this is the same behaviour with a checked type.

### F2. Invalid header config fails on every request, or is silently ignored

- Severity: medium.
- Location: `src/Middleware/ResponseHeaderMiddleware.php:41-58`.
- Evidence (scratch script `repro.php`, same result on router 3.20 and 4.3):

| Config | Result today |
| --- | --- |
| `'*' => 'oops'` (route not an array) | PHP warning `foreach() argument must be of type array\|object`, headers silently dropped |
| `'*' => ['X-A' => 'v']` (shorthand) | `TypeError: array_key_exists(): Argument #2 ($array) must be of type array, string given` on every request |
| `'value' => null` | `Laminas\Diactoros\Exception\InvalidArgumentException` on every request |
| `'overwrite' => 1` on an existing header | Silently treated as `false` |
| `'value' => 3600` | Works with Diactoros, but PSR-7 `withHeader()` only promises `string\|string[]` |

The errors surface per request, inside the response, after the handler already ran, and the message never names the config key.

Before:

```php
private array $config;

public function __construct(array $config)
{
    $this->config = $config;
}
```

After (full class, verified):

```php
/**
 * @phpstan-type HeaderConfig array{value: string|non-empty-list<string>, overwrite: bool}
 * @phpstan-type ResponseHeadersConfig array<string, array<string, HeaderConfig>>
 */
class ResponseHeaderMiddleware implements MiddlewareInterface
{
    private const ALL_ROUTES = '*';

    /** @var ResponseHeadersConfig */
    private readonly array $config;

    /**
     * @param array<mixed> $config
     * @throws InvalidConfigException
     */
    public function __construct(array $config)
    {
        $this->config = self::normalize($config);
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // as in F1
    }

    public function addHeaders(ResponseInterface $response, string $route): ResponseInterface
    {
        foreach ($this->config[$route] ?? [] as $header => $data) {
            if ($response->hasHeader($header) && ! $data['overwrite']) {
                continue;
            }
            $response = $response->withHeader($header, $data['value']);
        }

        return $response;
    }

    /**
     * @param array<mixed> $config
     * @return ResponseHeadersConfig
     * @throws InvalidConfigException
     */
    private static function normalize(array $config): array
    {
        $normalized = [];
        foreach ($config as $route => $headers) {
            if (! is_array($headers)) {
                throw InvalidConfigException::routeNotArray($route, $headers);
            }
            foreach ($headers as $header => $data) {
                if (! is_array($data)) {
                    throw InvalidConfigException::headerNotArray($route, $header, $data);
                }
                if (! array_key_exists('value', $data)) {
                    continue;
                }
                $overwrite = $data['overwrite'] ?? false;
                if (! is_bool($overwrite)) {
                    throw InvalidConfigException::invalidOverwrite($route, $header, $overwrite);
                }

                $normalized[(string) $route][(string) $header] = [
                    'value'     => self::normalizeValue($route, $header, $data['value']),
                    'overwrite' => $overwrite,
                ];
            }
        }

        return $normalized;
    }

    /**
     * @return string|non-empty-list<string>
     * @throws InvalidConfigException
     */
    private static function normalizeValue(int|string $route, int|string $header, mixed $value): string|array
    {
        if (is_string($value)) {
            return $value;
        }
        if (is_int($value) || is_float($value)) {
            return (string) $value;
        }
        if (! is_array($value) || $value === [] || ! array_is_list($value)) {
            throw InvalidConfigException::invalidValue($route, $header, $value);
        }

        $values = [];
        foreach ($value as $item) {
            if (! is_string($item)) {
                throw InvalidConfigException::invalidValue($route, $header, $value);
            }
            $values[] = $item;
        }

        return $values;
    }
}
```

New `src/Exception/InvalidConfigException.php`:

```php
<?php

declare(strict_types=1);

namespace Dot\ResponseHeader\Exception;

use InvalidArgumentException;

use function get_debug_type;
use function sprintf;

class InvalidConfigException extends InvalidArgumentException
{
    public static function routeNotArray(int|string $route, mixed $value): self
    {
        return new self(sprintf(
            'dot_response_headers.%s must be an array of headers, %s given.',
            $route,
            get_debug_type($value)
        ));
    }

    public static function headerNotArray(int|string $route, int|string $header, mixed $value): self
    {
        return new self(sprintf(
            'dot_response_headers.%s.%s must be an array with a "value" key, %s given.',
            $route,
            $header,
            get_debug_type($value)
        ));
    }

    public static function invalidValue(int|string $route, int|string $header, mixed $value): self
    {
        return new self(sprintf(
            'dot_response_headers.%s.%s.value must be a string, a number or a list of strings, %s given.',
            $route,
            $header,
            get_debug_type($value)
        ));
    }

    public static function invalidOverwrite(int|string $route, int|string $header, mixed $value): self
    {
        return new self(sprintf(
            'dot_response_headers.%s.%s.overwrite must be a bool, %s given.',
            $route,
            $header,
            get_debug_type($value)
        ));
    }
}
```

- Why: invalid config fails once, when the container builds the middleware, with a message naming the key. Entries without `value` are still skipped, as documented today. Valid configs behave exactly as before: the Mezzio integration check (T18) gives identical headers with the old and the new code.
- Breaking: `overwrite` must be a real `bool`, a non-array route or header entry throws instead of warning. Listed in the upgrade guide.

### F3. Empty `dot_response_headers` throws in the factory

- Severity: low.
- Location: `src/Factory/ResponseHeaderMiddlewareFactory.php:33-41`.
- Evidence: `'dot_response_headers' => []` throws `Exception: Unable to find dot-response-header config.`, although the middleware handles an empty config as a no-op (`process()` returns early). A `config` service that is an `ArrayObject` or `null` gives a `TypeError` from `array_key_exists()`. `$container->get('config')` is called twice, and the `?? []` is dead code.

Before:

```php
class ResponseHeaderMiddlewareFactory
{
    // constants unchanged

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws Exception
     */
    public function __invoke(ContainerInterface $container): ResponseHeaderMiddleware
    {
        if (! $container->has('config')) {
            throw new Exception(self::MESSAGE_MISSING_CONFIG);
        }
        $config = $container->get('config');

        if (
            ! array_key_exists('dot_response_headers', $config)
            || ! is_array($config['dot_response_headers'])
            || empty($config['dot_response_headers'])
        ) {
            throw new Exception(self::MESSAGE_MISSING_PACKAGE_CONFIG);
        }

        return new ResponseHeaderMiddleware($container->get('config')['dot_response_headers'] ?? []);
    }
}
```

After:

```php
final class ResponseHeaderMiddlewareFactory
{
    public const MESSAGE_MISSING_CONFIG         = 'Unable to find config.';
    public const MESSAGE_MISSING_PACKAGE_CONFIG = 'Unable to find dot-response-header config.';

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws InvalidConfigException
     */
    public function __invoke(ContainerInterface $container): ResponseHeaderMiddleware
    {
        if (! $container->has('config')) {
            throw new InvalidConfigException(self::MESSAGE_MISSING_CONFIG);
        }

        $config = $container->get('config');
        if (! is_array($config) && ! $config instanceof ArrayAccess) {
            throw new InvalidConfigException(self::MESSAGE_MISSING_CONFIG);
        }

        $headers = $config['dot_response_headers'] ?? null;
        if (! is_array($headers)) {
            throw new InvalidConfigException(self::MESSAGE_MISSING_PACKAGE_CONFIG);
        }

        return new ResponseHeaderMiddleware($headers);
    }
}
```

- Why: an empty array is a valid way to turn all headers off (for example per environment). A missing key still throws, so a forgotten config file is still reported. `InvalidConfigException` extends `InvalidArgumentException`, which extends `Exception`, so `catch (Exception)` keeps working and the message constants are unchanged.

### F4. Route headers cannot replace `*` headers unless `overwrite` is `true`

- Severity: documentation only (behaviour kept).
- Evidence: with `'*' => ['X-All' => ['value' => 'a']]` and `'home' => ['X-All' => ['value' => 'route']]`, a request to `home` gets `X-All: a`. `*` headers are applied first, so a route header is an "existing header" and needs `overwrite => true`. The existing test `testWillAddHeadersWithCommonWithRouteSpecificHeadersConfiguredWhenRouteMatched` asserts this, so it is intended.
- Change: say it in `usage.md` (D1). Also say that route keys match the **exact** route name; the README's "any route name matching 'user'" reads like a pattern.

### F5. `ConfigProvider` and the factory are not `final`

- Severity: low (API hygiene).
- Before: `class ConfigProvider`, `class ResponseHeaderMiddlewareFactory`.
- After: `final class ...` for both. `ResponseHeaderMiddleware` stays non-final and `addHeaders()` stays public, since applications may extend or call it.
- Why: same choice as `dot-cache` 5.0. Breaking for anyone extending them; listed in the upgrade guide.

### F6. `ConfigProvider` return types

- Location: `src/ConfigProvider.php:12,19` (V5 #1 and #2).

Before:

```php
public function __invoke(): array
```

After:

```php
/**
 * @return array{dependencies: array{factories: array<class-string, class-string>}}
 */
public function __invoke(): array
```

and `@return array{factories: array<class-string, class-string>}` on `getDependencies()`.

### F7. Coverage gaps (T16)

Measured by reading the tests (no coverage driver here, so line numbers are not from a report):

- `process()` is only called with an empty config, so lines 33-38 (common headers, route lookup) never run.
- The factory success path is never tested; neither is a non-array `config`.
- `ConfigProviderTest` only asserts `assertIsArray`, which is always true.
- `addHeaders()` on a missing `value` key (line 45-47) is not tested.
- Route failure (404/405 `RouteResult`) is not tested.

### F8. Rewrite the tests (T8, T16)

- Replace mocks with real collaborators: `Laminas\Diactoros\ServerRequest`, a real `RouteResult::fromRoute()` / `fromRouteFailure()`, and three fixtures in `test/Fixture/`:
    - `ArrayContainer` (`final readonly`, PSR-11 `get()` / `has()` over an array; same as `dot-cache`).
    - `ServiceNotFoundException extends RuntimeException implements NotFoundExceptionInterface`.
    - `FixedResponseHandler` (`RequestHandlerInterface` returning one `Response`, optionally with pre-set headers).
- Data providers are `public static` with `#[DataProvider]`; test classes are `final`.
- Prototype: 27 tests, 42 assertions, OK on PHPUnit 12.5.38 and 12.5.43, phpcs clean, PHPStan level 8 and `max` clean. **14 of the 27 fail against the current `src/`** (the bug tests for F2 and F3).

| Test class | Cases |
| --- | --- |
| `ConfigProviderTest` | `__invoke()` wraps `getDependencies()`; the middleware maps to its factory |
| `ResponseHeaderMiddlewareFactoryTest` | Missing `config`; non-array `config`; package key missing / string / null (provider); empty package config is a no-op; `ArrayObject` config; invalid package config fails at creation |
| `ResponseHeaderMiddlewareTest` | Empty config returns the handler response unchanged; no `RouteResult`; route failure; matched route with `overwrite` true/false against `*`; route without config; handler header kept unless `overwrite`; header without `value` skipped; valid values string / int / float / list (provider); 6 invalid configs with exact messages (provider) |

Before (excerpt):

```php
public function setUp(): void
{
    $this->responseHeader = new ResponseHeaderMiddleware([]);
    $this->serverRequest  = $this->createMock(ServerRequestInterface::class);
    $this->requestHandler = $this->createMock(RequestHandlerInterface::class);
}
```

After (excerpt):

```php
public function testMatchedRouteAddsRouteHeadersAfterCommonHeaders(): void
{
    $response = (new ResponseHeaderMiddleware(self::CONFIG))
        ->process($this->requestForRoute('home'), new FixedResponseHandler());

    $this->assertSame('home', $response->getHeaderLine('X-Home'));
    $this->assertSame('home', $response->getHeaderLine('X-All'), 'route header with overwrite replaces *');
    $this->assertSame('all', $response->getHeaderLine('X-Overwrite'), 'route header without overwrite keeps *');
}
```

- Coverage driver: none installed here. CI already uses pcov in `codecov.yml`. Mutation testing (`infection/infection`) is optional for a package this size; not recommended as a new dev dependency now.

### F9. Integration check (T18)

- Scratch Mezzio 3 application (laminas-servicemanager, FastRoute, router 3.20) with `RouteMiddleware`, this middleware, `DispatchMiddleware`, `NotFoundHandler`, and the README config pasted verbatim.
- Result, old and new code identical: `/` gets `CustomHeader1` overwritten, `CustomHeader2` kept from the handler, `CustomHeader` added; `/login` gets `LoginHeader`; `/missing` (404) gets only the `*` headers.
- Not run against router 4.x with a real router implementation (no FastRoute in the 4.x vendor here).

## Documentation

`docs/book/index.md` is a symlink to `../../README.md`, so README edits are the docs home page.

| Page | Required change |
| --- | --- |
| `docs/book/v3/*` | Unchanged |
| `docs/book/v4/` | New folder, copied from `v3` |
| `v4/overview.md` | Badge `php-v/.../3.6.0` to `4.0.0`; `?branch=4.0` and `blob/4.0/LICENSE` |
| `v4/installation.md` | `composer require dotkernel/dot-response-header:^4.0` |
| `v4/configuration.md` | PHP 8.3 to 8.6; `value` accepts string, number or list of strings; `overwrite` must be a bool; empty `dot_response_headers` is allowed; invalid config throws `InvalidConfigException` naming the key |
| `v4/usage.md` | `*` first, then the exact route name (F4); route header needs `overwrite => true` to replace a `*` header; middleware must be piped after `RouteMiddleware` or route headers never apply; example of a multi-value header |
| `v4/upgrading.md` | New, content below |
| `README.md` | Versions table (below); "PHP: 8.3, 8.4, 8.5 or 8.6"; badge `3.6.0` to `4.0.0`; "matching 'user'" wording (F4); missing trailing comma in the `LoginHeader` example |
| `SECURITY.md` | Rows below; fix the double blank line (MD012) and the typo "newc" |
| `mkdocs.yml` | `current_version: v4`; `versions: [v4, v3]`; `v4` nav first with Overview, Installation, Configuration, Usage, Upgrading |
| `CHANGELOG.md` | D2 |

README and `SECURITY.md` versions table:

| Version | Supported | PHP |
| --- | --- | --- |
| 4.x | :white_check_mark: | 8.3 to 8.6 |
| 3.x | Security fixes only | 8.2 to 8.5 |
| <= 2.x | :x: | |

All existing PHP snippets in README, `v3` docs and `config/response-header.global.php.dist` pass `php -l` (the fragment in `usage.md` was wrapped in an array to lint it). The README config example runs unchanged in the integration check.

### D3. Upgrade guide (`v4/upgrading.md`)

| Change | Symptom | Fix |
| --- | --- | --- |
| PHP 8.2 dropped | Composer refuses to install | PHP 8.3 or newer |
| `overwrite` must be a `bool` | `InvalidConfigException: dot_response_headers.<route>.<header>.overwrite must be a bool, int given.` | Use `true` / `false`. In 3.x, `1` or `'true'` were silently treated as `false` |
| Route and header entries must be arrays | `InvalidConfigException` naming the key | In 3.x a string route entry was ignored with a PHP warning, and a string header entry threw `TypeError` per request |
| `value` must be a string, number or non-empty list of strings | `InvalidConfigException` | In 3.x `null` threw from the PSR-7 implementation on every request |
| Factory throws `InvalidConfigException` | Code catching `Exception` still works; code checking `get_class()` or the exact class does not | Catch `InvalidArgumentException` or `InvalidConfigException` |
| `ConfigProvider` and factory are `final` | `Class ... cannot extend final class` | Decorate or write your own factory |

Changes in behaviour: empty `dot_response_headers` now creates a no-op middleware; numeric values are sent as strings; invalid config fails when the middleware is created instead of on each request.

## CHANGELOG

The file has only `3.0.0` and `3.0.1`, oldest first, with `3.0.1` dated `2021-01-18` (the tag is `2021-05-18`). It fails markdownlint (MD041, MD022, MD032). Rewrite newest first, add `# Changelog`, blank lines around headings and lists, `-` bullets, and keep the Changed / Added / Deprecated / Removed / Fixed sections with "Nothing" where empty. Entries from the git history between tags:

| Version | Date | Content |
| --- | --- | --- |
| 4.0.0 | TBD | Changed: PHP `~8.3.0 \|\| ~8.4.0 \|\| ~8.5.0 \|\| ~8.6.0`; `mezzio-router` `^3.16 \|\| ^4.0`; config validated on construction (F2); factory throws `InvalidConfigException`; `ConfigProvider` and factory `final`; PHPUnit 12, PHPStan level 8, `failOn*`; actions v7/v6/v7. Added: PHP 8.6, router 4, `InvalidConfigException`, explicit `psr/container` and `psr/http-server-*` requirements, `v4` docs and upgrade guide. Removed: PHP 8.2, `psr/http-client` (unused). Fixed: empty config throwing (F3), `TypeError` for non-array `config`, warnings and per-request errors for bad config |
| 3.6.0 | 2025-10-30 | Removed PHP 8.1; added PHP 8.4 and 8.5 |
| 3.5.0 | 2025-03-12 | Replaced Psalm with PHPStan |
| 3.4.1 | 2025-01-28 | Psalm 6; "DotKernel" spelled "Dotkernel" |
| 3.4.0 | 2024-10-22 | PHP 8.4; laminas-coding-standard 3 |
| 3.3.1 | 2024-05-27 | `psr/http-message` back to `^1.0 \|\| ^2.0` |
| 3.3.0 | 2024-05-03 | v3 documentation; `psr/http-message` `^2.0` only; SECURITY unsupported versions |
| 3.2.3 | 2023-11-30 | PHP 8.3 |
| 3.2.2 | 2023-08-22 | Codecov workflow and badges |
| 3.2.1 | 2023-08-16 | `psr/http-message` `^1.0 \|\| ^2.0`; diactoros `^3.1` |
| 3.2.0 | 2023-07-28 | PHP `~8.1.0 \|\| ~8.2.0`; required `mezzio-router`, `psr/http-client`, `psr/http-message`; tests, laminas-coding-standard, Psalm; `DotTest` autoload |
| 3.1.0 | 2022-04-06 | PHP `~7.4.0 \|\| ~8.0.0 \|\| ~8.1.0`; PHPUnit 9.5 |
| 3.0.1 | 2021-05-18 | CHANGELOG added; README and middleware changes |
| 3.0.0 | 2021-05-14 | Initial release |

## Rollout order

1. ~~Maintainer: apply the `composer.json` edits (V1, V3) and run `composer update`.~~ Done; baseline re-run in the repository on 2026-11-11.
2. ~~`phpunit.xml` and `.gitignore` (V6).~~ Done: PHPUnit exits 1 on the 5 old notices.
3. ~~`ConfigProvider` types and `final` (F5, F6).~~ Done: PHPStan 7 to 5 errors, phpcs clean.
4. ~~Tests rewritten with fixtures (F8), first against the current `src/`.~~ Done: 14 expected failures confirmed.
5. ~~`InvalidConfigException`, middleware (F1, F2), factory (F3).~~ Done: `composer check` passes.
6. ~~Workflows (V4).~~ Done.
7. ~~Docs `v4`, README, SECURITY, mkdocs (D1, D3).~~ Done; lint-clean.
8. ~~CHANGELOG (D2).~~ Done; lint-clean.
9. Maintainer: review, merge `upgrade` into `4.0`, tag `4.0.0`, CHANGELOG date; then re-enable `backwardCompatibilityCheck` in `.laminas-ci.json`.

## Verification checklist

- [x] `composer validate --strict` (valid after the maintainer's edits)
- [x] `composer why-not php 8.6.0` and `8.3.0`: no blocker
- [x] `composer check` (phpcs, PHPUnit, PHPStan) passes in the repository: 27 tests, 42 assertions, PHPStan level 8 clean
- [x] `vendor/bin/phpstan analyse --level max`: clean
- [x] No `MockObject` left in `test/`: `grep -rn "createMock\|MockObject" test` finds nothing
- [x] Every new bug test fails on the 3.6.0 code and passes after the fix (14 failed before step 5)
- [x] `php -l` on every PHP snippet in README and `docs/book/v4` (the one-line `ConfigProvider::class,` fragment linted inside an array)
- [x] Every v4 usage and configuration example run through the middleware; output matches what the docs say
- [x] Every `mkdocs.yml` nav target exists
- [ ] `mkdocs build --strict` (not installed here)
- [x] markdownlint passes on README, SECURITY, CHANGELOG, `docs/book/v4/*.md` and this plan with `~/.claude/.markdownlint.jsonc`
- [x] No CRLF line endings; every changed file ends with a newline
- [ ] CI green on 8.3, 8.4, 8.5 and 8.6, lowest and latest dependencies
- [ ] Unverified here, to confirm in CI:
    - [ ] PHP 8.3, 8.4 and 8.6 (only 8.5.12 is installed locally)
    - [ ] Lowest dependencies: `mezzio-router` 3.17.0 (8.3), 3.18.0 (8.4), 3.19.0 (8.5), 4.3.0 (8.6); `psr/container` 1.1; `psr/http-message` 1.0; diactoros 3.10.0
    - [x] Which `mezzio-router` release first declares PHP 8.6: 4.3.0 (Packagist metadata; 4.0.x stops at 8.4)
    - [ ] Whether `ignore_php_platform_requirements` for 8.6 can be removed from `.laminas-ci.json`
    - [ ] pcov availability for PHP 8.6 on `setup-php` (only matters if codecov keeps 8.6)
    - [ ] Line coverage numbers (no pcov or Xdebug locally)
    - [ ] Integration with router 4.x and a real router implementation
    - [ ] Vendor deprecations on PHP 8.6 with `failOnDeprecation`
