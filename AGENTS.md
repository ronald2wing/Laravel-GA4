# AGENTS.md

## Commands

```bash
composer check       # lint (dry-run) → test — run before committing
composer test        # PHPUnit
composer test-filter # e.g. composer test-filter render
composer lint        # Pint dry-run
composer pint        # Pint auto-fix
composer analyse     # PHPStan level 5 (--memory-limit=2G baked in)
```

## Architecture

- `src/Ga4Tag.php` — renders `<script>` tags with GA4 tracking code. Returns `''` when ID is unset/invalid. Implements `Htmlable` + `Stringable`.
- `src/Ga4ServiceProvider.php` — registers `Ga4Tag` as a singleton and the `@ga4` Blade directive.
- `config/ga4.php` — `['measurement_id' => env('GA4_MEASUREMENT_ID', ''), 'parameters' => []]`
- Autoload PSR-4: `Ronald2Wing\LaravelGa4\` → `src/`, tests → `tests/`
- Auto-discovered via `extra.laravel`. No manual provider registration.

## Configuration flow

```
.env (GA4_MEASUREMENT_ID)
  → config/ga4.php (env() helper)
    → Ga4ServiceProvider::register() (mergeConfigFrom + singleton)
      → Ga4Tag::fromConfig() (validates and normalizes)
```

## Measurement ID validation

Regex: `/^G-[A-Z0-9_-]+$/` — **uppercase only**. Lowercase, unicode, embedded whitespace, and special chars are rejected.

`normalizeId()` trims the input before testing the pattern. Blank or invalid strings → `null` → `render()` returns `''`.

## Singleton freeze

The `Ga4Tag` singleton resolves once, consuming the current config. Subsequent `config()->set('ga4.measurement_id', ...)` calls are ignored. Tested in `Ga4ServiceProviderTest::test_singleton_freezes_config_at_first_resolution`.

## XSS safety

Two private methods must be preserved when modifying `render()`:

| Method                    | What it does                                                                                        |
| ------------------------- | --------------------------------------------------------------------------------------------------- |
| `escapeAttribute($value)` | `htmlspecialchars(value, ENT_QUOTES\|ENT_SUBSTITUTE\|ENT_HTML5, 'UTF-8')`                           |
| `encodeJson($value)`      | `json_encode(value, JSON_THROW_ON_ERROR\|JSON_HEX_TAG\|JSON_HEX_AMP\|JSON_HEX_APOS\|JSON_HEX_QUOT)` |

The `JSON_THROW_ON_ERROR` flag means any non-encodable value will throw — keep value types safe.

## Blade directive

`@ga4` compiles to:

```php
<?php echo app(\Ronald2Wing\LaravelGa4\Ga4Tag::class)->render(); ?>
```

Resolve `Ga4Tag` via `app(Ga4Tag::class)` for `isEnabled()` checks in controllers/middleware.

## Testing

- `Ga4TagTest` / `Ga4TagValidationTest` — extend `PHPUnit\Framework\TestCase` (fast, no Laravel boot)
- `Ga4ServiceProviderTest` — extends `Orchestra\Testbench\TestCase` (full Laravel boot, slow)
- Use `#[Test]` and `#[DataProvider]` attributes, not docblock annotations
- Coverage output: `coverage/clover.xml` (CI/Codecov)

## CI quirks

- Branch: `master`
- PHP 8.3–8.5 × Laravel 11–13 in matrix. **Laravel 10 is excluded from all PHP versions** despite being in `composer.json require`.
- CI order: `test` → `lint` → `analyse` (differs from `composer check` which runs lint first)
- `composer.lock` is gitignored (library convention)
- `composer.json` `platform.php: 8.3.0` pins dependency resolution regardless of local PHP
