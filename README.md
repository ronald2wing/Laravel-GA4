# Laravel GA4 Package

[![Latest Version on Packagist](https://img.shields.io/packagist/v/ronald2wing/laravel-ga4.svg?style=flat-square)](https://packagist.org/packages/ronald2wing/laravel-ga4)
[![Total Downloads](https://img.shields.io/packagist/dt/ronald2wing/laravel-ga4.svg?style=flat-square)](https://packagist.org/packages/ronald2wing/laravel-ga4)
[![License](https://img.shields.io/packagist/l/ronald2wing/laravel-ga4.svg?style=flat-square)](LICENSE)
[![PHP Version](https://img.shields.io/packagist/php-v/ronald2wing/laravel-ga4.svg?style=flat-square)](https://packagist.org/packages/ronald2wing/laravel-ga4)
[![Test Coverage](https://img.shields.io/badge/coverage-100%25-brightgreen.svg?style=flat-square)](https://github.com/ronald2wing/laravel-ga4)

A Laravel package for seamless Google Analytics 4 (GA4) integration with automatic Livewire SPA navigation tracking.

## 🚀 Quick Installation

```bash
composer require ronald2wing/laravel-ga4
```

Add your GA4 Measurement ID to `.env`:

```env
GA4_MEASUREMENT_ID=G-XXXXXXXXXX
```

Add to your Blade layout:

```blade
{!! ga4() !!}
```

## 📖 Table of Contents

- [✨ Features](#-features)
- [📋 Requirements](#-requirements)
- [🚀 Quick Start](#-quick-start)
- [📖 Usage](#-usage)
- [⚡ Livewire Integration](#-livewire-integration)
- [⚙️ Configuration](#️-configuration)
- [🔧 Troubleshooting](#-troubleshooting)
- [🧪 Testing](#-testing)
- [📦 Dependencies](#-dependencies)
- [📄 License](#-license)

## ✨ Why This Package?

Integrating Google Analytics 4 into Laravel applications can be tedious, especially when dealing with manual script injection, SPA navigation tracking for Livewire applications, and environment-specific configuration.

This package solves these problems with a simple, elegant API that "just works".

## 🚀 Key Features

- **Automatic GA4 Script Injection** – Single line of code in your Blade templates
- **Livewire SPA Detection** – Automatically detects and integrates with Livewire
- **Environment-Aware** – Only renders when properly configured
- **Security First** – Proper HTML escaping and safe JavaScript injection
- **Performance Optimized** – Minimal overhead, no database queries
- **SPA Navigation Tracking** – Automatic page view tracking for Livewire applications
- **Disabled Auto Page Views** – Better control over when page views are tracked
- **Configurable** – Publishable configuration for customization
- **Full Test Coverage** – Comprehensive unit tests with edge cases

## 📋 Requirements

- **PHP**: ^8.2 (8.2 or higher)
- **Laravel**: ^10.x|^11.x|^12.x
- **Composer**: Latest version recommended

### Optional Dependencies

- **Livewire**: For SPA navigation tracking (auto-detected)

## 🚀 Quick Start

### 1. Install the Package

```bash
composer require ronald2wing/laravel-ga4
```

### 2. Configure Your GA4 Measurement ID

Add your GA4 Measurement ID to your `.env` file:

```env
GA4_MEASUREMENT_ID=G-XXXXXXXXXX
```

**Where to find your Measurement ID:**

1. Go to [Google Analytics](https://analytics.google.com/)
2. Navigate to **Admin → Data Streams → [Your Stream]**
3. Copy the **Measurement ID** (starts with "G-")

### 3. Add to Your Layout

In your main layout file (typically `resources/views/layouts/app.blade.php`), add the GA4 script before the closing `</body>` tag:

```blade
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', config('app.name'))</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    @yield('content')

    <!-- Add GA4 tracking script -->
    {!! ga4() !!}
</body>
</html>
```

That's it! Your GA4 tracking is now live.

## 📖 Detailed Usage

### Basic Usage in Blade Templates

The simplest way to use the package is with the `ga4()` helper function:

```blade
<!-- In any Blade template -->
{!! ga4() !!}
```

### Programmatic Access

You can also access the GA4 service programmatically using the facade:

```php
use Ronald2Wing\LaravelGa4\Facades\Ga4;

// Get the rendered script
$script = Ga4::render();

// Check if GA4 is configured
if (Ga4::render() !== '') {
    // GA4 is active and ready
}
```

### Conditional Rendering Examples

```blade
@if(app()->environment('production'))
    {!! ga4() !!}
@endif
```

## ⚡ Livewire Integration

### Automatic SPA Navigation Tracking

When Livewire is installed, the package automatically adds SPA navigation tracking. This means page views are tracked when users navigate between Livewire components without full page reloads.

### How It Works

1. **Automatic Detection**: The package checks if `Livewire\Livewire` class exists
2. **Event Listening**: Listens to `livewire:navigated` events
3. **Page View Tracking**: Sends GA4 page_view events on navigation

### Installing Livewire

```bash
composer require livewire/livewire
```

**Note**: Livewire is optional. The package works perfectly without it, but SPA navigation tracking won't be available.

## ⚙️ Configuration

### Configuration File

Publish the configuration file to customize settings:

```bash
php artisan vendor:publish --tag=ga4-config
```

This creates `config/ga4.php`:

```php
return [
    /*
    |--------------------------------------------------------------------------
    | Google Analytics 4 Measurement ID
    |--------------------------------------------------------------------------
    |
    | Your GA4 measurement ID (format: G-XXXXXXXXXX).
    |
    */
    'measurement_id' => env('GA4_MEASUREMENT_ID', ''),
];
```

### Environment Variables

The package uses a single environment variable:

```env
# Required for GA4 tracking
GA4_MEASUREMENT_ID=G-XXXXXXXXXX
```

## 🔧 Troubleshooting

### Common Issues & Solutions

#### Script not rendering

- **Symptoms**: No GA4 script in HTML output
- **Solution**:
  1. Check `.env` has `GA4_MEASUREMENT_ID`
  2. Verify ID format: `G-XXXXXXXXXX`
  3. Clear config cache: `php artisan config:clear`

#### Livewire navigation not tracked

- **Symptoms**: Page views only on full reloads
- **Solution**:
  1. Install Livewire: `composer require livewire/livewire`
  2. Verify Livewire is properly installed

#### JavaScript errors

- **Symptoms**: Console shows GA4-related errors
- **Solution**:
  1. Verify measurement ID is valid
  2. Check for typos in ID
  3. Ensure ID starts with "G-"

#### Double page views

- **Symptoms**: Duplicate page_view events in GA4
- **Solution**:
  1. Package disables auto page_view
  2. Check for manual `gtag('config')` calls

### Debugging Steps

1. **Check Configuration**:

   ```bash
   php artisan tinker
   >>> config('ga4.measurement_id')
   ```

2. **Verify Script Generation**:

   ```bash
   php artisan tinker
   >>> ga4()
   ```

## 🛡️ Security Best Practices

### 1. Never Commit Sensitive Data

```bash
# Add to .gitignore
.env
.env.local
.env.*.local
```

### 2. Use Different IDs Per Environment

```env
# Development (optional - can be empty)
GA4_MEASUREMENT_ID_DEVELOPMENT=

# Staging
GA4_MEASUREMENT_ID_STAGING=G-STG123456

# Production
GA4_MEASUREMENT_ID_PRODUCTION=G-PRO123456
```

### 3. Regular Security Updates

```bash
# Check for vulnerabilities
composer audit

# Update dependencies
composer update
```

## ⚡ Performance Considerations

### Minimal Overhead

- **No Database Queries**: Configuration is loaded from memory
- **No External API Calls**: Script generation is purely local
- **Single Generation**: Script is generated once per request when called
- **Caching Friendly**: Works with Laravel's caching mechanisms

## 🧪 Testing

### Running Tests

```bash
# Run all tests
composer test

# Generate coverage report (HTML)
composer test-coverage

# Run tests with verbose output
composer test-verbose

# Run specific test method(s) by name pattern(s)
composer test-filter testRenderReturnsEmptyStringWhenNotConfigured

# Static analysis with PHPStan (max level)
composer analyze

# Lint all PHP files
composer lint-all

# Combined check (lint + test)
composer check
```

### Test Coverage

The package has 100% test coverage. Coverage reports are generated in the `coverage/` directory.

### Test Suite Structure

The package includes comprehensive unit tests covering:

- Configuration validation
- Livewire integration
- Edge cases
- Service provider registration
- Facade access
- Helper functions

## 🛠️ Development

### Development Commands

```bash
# Install dependencies
composer install

# Run full test suite with linting check
composer check

# Update dependencies
composer update

# Check for outdated packages
composer outdated

# Check for security vulnerabilities
composer audit

# Run all tests
composer test

# Generate coverage report (HTML)
composer test-coverage

# Run tests with verbose output
composer test-verbose

# Run specific test method(s) by name pattern(s)
composer test-filter testRenderReturnsEmptyStringWhenNotConfigured

# Lint all PHP files (syntax check)
composer lint-all

# Check code style with Pint (dry run)
composer pint-test

# Fix code style issues with Pint
composer pint-fix

# Run Pint
composer pint

# Static analysis with PHPStan (max level)
composer analyze
```

### Project Structure

```text
laravel-ga4/
├── config/
│   └── ga4.php              # Package configuration
├── src/
│   ├── Facades/
│   │   └── Ga4.php          # Facade for service access
│   ├── Ga4Service.php       # Core service class
│   ├── Ga4ServiceProvider.php # Service provider
│   └── helpers.php          # Helper functions
├── tests/
│   └── Unit/               # Comprehensive test suite
├── composer.json           # Package definition
└── README.md              # This file
```

## 📊 Version Compatibility

| Laravel Version | Package Compatibility | PHP Version |
| --------------- | --------------------- | ----------- |
| 10.x            | ✅ Fully Supported    | ^8.2        |
| 11.x            | ✅ Fully Supported    | ^8.2        |
| 12.x            | ✅ Fully Supported    | ^8.2        |

## 📦 Dependencies

### Required Dependencies

- **PHP**: ^8.2
- **illuminate/support**: ^10.0|^11.0|^12.0

### Development Dependencies

- **orchestra/testbench**: ^8.0
- **phpunit/phpunit**: ^10.0
- **laravel/pint**: ^1.26

### Suggested Dependencies

- **livewire/livewire**: For SPA navigation tracking

## 📄 License

This package is open-sourced software licensed under the [MIT license](LICENSE).

## 🤝 Support

### Getting Help

- **GitHub Issues**: [Report bugs or request features](https://github.com/ronald2wing/laravel-ga4/issues)
- **Documentation**: This README and source code comments

### Reporting Issues

When reporting issues, please include:

1. Laravel version
2. Package version
3. PHP version
4. Error messages or logs
5. Steps to reproduce

## 📝 Changelog

All notable changes to this project will be documented in this section.

### [1.0.0] - 2025-12-24

#### Added

- Initial release with core GA4 tracking functionality
- Automatic Livewire SPA navigation tracking detection
- Comprehensive test suite with 100% coverage
- Configuration file with environment variable support
- Helper function `ga4()` for easy Blade integration
- Facade `Ga4::render()` for programmatic access

For a detailed view of all changes, see the [GitHub commit history](https://github.com/ronald2wing/laravel-ga4/commits/master) and [CHANGELOG.md](CHANGELOG.md).

## 📚 Additional Resources

- [Google Analytics 4 Documentation](https://developers.google.com/analytics/devguides/collection/ga4)
- [Laravel Documentation](https://laravel.com/docs)
- [Livewire Documentation](https://livewire.laravel.com/docs)
- [Packagist Page](https://packagist.org/packages/ronald2wing/laravel-ga4)

---

Made with ❤️ by [Ronald2Wing](https://github.com/ronald2wing)

If this package saves you time, please consider ⭐ starring the repository!

[Support the developer](https://github.com/sponsors/ronald2wing) •
[Report an issue](https://github.com/ronald2wing/laravel-ga4/issues) •
[Contribute](https://github.com/ronald2wing/laravel-ga4/pulls)
