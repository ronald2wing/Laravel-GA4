# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.0] - 2025-12-24

### 🎉 Initial Release

First stable release of Laravel GA4 package with seamless Google Analytics 4 integration and automatic Livewire SPA navigation tracking.

### Added

- **Core GA4 Tracking**: Integration with Google Analytics 4 via measurement ID
- **Livewire SPA Support**: Automatic detection and tracking for Livewire Single Page Applications
- **Blade Helper Function**: `ga4()` helper for easy integration in Blade templates
- **Facade Support**: `Ga4` facade for programmatic access
- **Service Provider**: Laravel service provider with automatic registration
- **Configuration Publishing**: Publishable configuration file via `php artisan vendor:publish --tag=ga4-config`
- **Environment Awareness**: Only renders tracking script when measurement ID is configured
- **Secure JavaScript Injection**: Proper HTML escaping and safe script generation
- **Comprehensive Test Suite**: 118 tests with 286 assertions and 100% test coverage

### Changed

- **Disabled Auto Page Views**: GA4 auto page_view tracking disabled for better control
- **Zero Configuration Required**: Works with sensible defaults out of the box

### Security

- **HTML Escaping**: All output properly escaped using `htmlspecialchars()`
- **JavaScript Safety**: Measurement ID JSON-encoded with hex encoding
- **Input Validation**: Measurement ID validated as non-empty string
- **No Sensitive Data Exposure**: Only measurement ID exposed in output

### Technical Details

- **PHP Compatibility**: ^8.2 (PHP 8.2 or higher required)
- **Laravel Compatibility**: ^10.0|^11.0|^12.0 (Laravel 10, 11, and 12 compatible)
- **Livewire Compatibility**: Optional dependency for SPA navigation tracking
- **Development Tools**: PHPStan for static analysis, Laravel Pint for code style
- **CI/CD**: GitHub Actions workflow for automated testing
- **Code Coverage**: 100% test coverage maintained

### Features

- One-line Blade integration with `{!! ga4() !!}`
- Automatic Livewire detection for SPA navigation tracking
- Performance optimized with minimal overhead
- PSR-12 compliant code style
- MIT licensed

## Versioning Policy

This project follows [Semantic Versioning](https://semver.org/spec/v2.0.0.html):

- **MAJOR** version for incompatible API changes
- **MINOR** version for new functionality in a backward compatible manner
- **PATCH** version for backward compatible bug fixes

## Release Process

1. **Version Bump**: Update version in `composer.json`
2. **Changelog Update**: Add new version section to CHANGELOG.md
3. **Tag Release**: Create Git tag with version number
4. **GitHub Release**: Create release on GitHub with release notes
5. **Packagist Update**: Wait for Packagist to auto-update

## Links

[1.0.0]: https://github.com/ronald2wing/laravel-ga4/releases/tag/1.0.0
