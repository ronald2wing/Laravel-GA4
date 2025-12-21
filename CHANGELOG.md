# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.0] - 2026-04-26

### Added

- GA4 tracking via a measurement ID read from `GA4_MEASUREMENT_ID`.
- `@ga4` Blade directive that renders the gtag.js snippet — or nothing, when disabled.
- Auto-discovered service provider with publishable config (`php artisan vendor:publish --tag=ga4-config`).
- HTML-escaped, JSON-hex-encoded script injection.
- Configurable parameters forwarded to `gtag('config', id, parameters)`.
- `Ga4Tag::isEnabled()` for conditional rendering.
- Support for Laravel 10–13 on PHP 8.3+.
