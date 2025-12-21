# Laravel GA4

[![Latest Version on Packagist](https://img.shields.io/packagist/v/ronald2wing/laravel-ga4.svg?style=flat-square)](https://packagist.org/packages/ronald2wing/laravel-ga4)
[![Total Downloads](https://img.shields.io/packagist/dt/ronald2wing/laravel-ga4.svg?style=flat-square)](https://packagist.org/packages/ronald2wing/laravel-ga4)
[![License](https://img.shields.io/packagist/l/ronald2wing/laravel-ga4.svg?style=flat-square)](LICENSE)
[![PHP Version](https://img.shields.io/packagist/php-v/ronald2wing/laravel-ga4.svg?style=flat-square)](https://packagist.org/packages/ronald2wing/laravel-ga4)
[![Test Coverage](https://img.shields.io/badge/coverage-100%25-brightgreen.svg?style=flat-square)](https://github.com/ronald2wing/laravel-ga4)
[![PHPStan](https://img.shields.io/badge/PHPStan-level%20max-brightgreen.svg?style=flat-square)](https://github.com/ronald2wing/laravel-ga4)
[![GitHub Actions](https://img.shields.io/github/actions/workflow/status/ronald2wing/laravel-ga4/php.yml?branch=master&style=flat-square)](https://github.com/ronald2wing/laravel-ga4/actions)

A lightweight Laravel package for seamless Google Analytics 4 (GA4) integration with Blade directives and secure JavaScript injection.

## ✨ Features

- **One-line Integration** – Add GA4 tracking with `{!! ga4() !!}` in Blade templates
- **Secure JavaScript Injection** – Proper HTML escaping and safe script generation
- **Environment Aware** – Only renders when measurement ID is configured
- **Disabled Auto Page Views** – Better control over when page views are tracked
- **Zero Configuration** – Works with sensible defaults out of the box
- **100% Test Coverage** – Comprehensive test suite with edge cases
- **Laravel 10-12 Support** – Compatible with modern Laravel versions
- **Publishable Configuration** – Customize settings when needed

## 📋 Requirements

- **PHP**: 8.2 or higher
- **Laravel**: 10.x, 11.x, or 12.x
- **Composer**: Latest version recommended

## 🚀 Quick Start

### 1. Installation

```bash
composer require ronald2wing/laravel-ga4
```

The package automatically registers its service provider and facade.

### 2. Configuration

Add your GA4 Measurement ID to `.env`:

```env
GA4_MEASUREMENT_ID=G-XXXXXXXXXX
```

**How to find your Measurement ID:**

1. Go to [Google Analytics](https://analytics.google.com/)
2. Navigate to **Admin → Data Streams → [Your Stream]**
3. Copy the **Measurement ID** (starts with "G-")

### 3. Usage in Blade Templates

Add the tracking script to your Blade layout:

```blade
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Application</title>
</head>
<body>
    <!-- Your application content -->

    <!-- Add GA4 tracking before closing body tag -->
    {!! ga4() !!}
</body>
</html>
```

## ⚙️ Configuration

### Environment Configuration

The package uses a single environment variable:

```env
GA4_MEASUREMENT_ID=G-XXXXXXXXXX
```

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

## 📖 Usage Guide

### Basic Usage

The simplest way to use the package is with the `ga4()` helper function:

```blade
<!-- In any Blade template, typically in your layout file -->
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
<!-- Only render in production environment -->
@if(app()->environment('production'))
    {!! ga4() !!}
@endif

<!-- Only render for authenticated users -->
@auth
    {!! ga4() !!}
@endauth

<!-- Only render when measurement ID is set -->
@if(config('ga4.measurement_id'))
    {!! ga4() !!}
@endif

<!-- Combine multiple conditions -->
@if(app()->environment('production') && config('ga4.measurement_id'))
    {!! ga4() !!}
@endif
```

## 🔧 Advanced Usage

### Custom JavaScript Integration

If you need to integrate with custom JavaScript, you can access the generated script:

```php
use Ronald2Wing\LaravelGa4\Facades\Ga4;

// Get the raw JavaScript (without script tags)
$service = app('ga4');
$measurementId = config('ga4.measurement_id');

// Or use the facade to get complete HTML
$html = Ga4::render();
```

### Multiple Environments Setup

For different environments, use different measurement IDs:

```env
# Development (optional - can be empty)
GA4_MEASUREMENT_ID=

# Staging
GA4_MEASUREMENT_ID=G-STG123456

# Production
GA4_MEASUREMENT_ID=G-PRO123456
```

## 🐛 Troubleshooting

### Common Issues & Solutions

#### Script Not Rendering

**Symptoms**: No GA4 script in HTML output

**Solutions**:

1. Check `.env` has `GA4_MEASUREMENT_ID` set
2. Verify ID format: `G-XXXXXXXXXX`
3. Clear config cache: `php artisan config:clear`
4. Check if measurement ID is empty or invalid

#### JavaScript Errors

**Symptoms**: Console shows GA4-related errors

**Solutions**:

1. Verify measurement ID is valid
2. Check for typos in ID
3. Ensure ID starts with "G-"
4. Check network tab for failed script loads

#### Double Page Views

**Symptoms**: Duplicate `page_view` events in GA4

**Solutions**:

1. Package disables auto `page_view` by default
2. Check for manual `gtag('config')` calls in your code
3. Verify no other GA4 scripts are loaded

## 🔒 Security

### Security Features

- **HTML Escaping**: All output is properly escaped using `htmlspecialchars()`
- **JavaScript Safety**: Measurement ID is JSON-encoded with hex encoding
- **Input Validation**: Measurement ID is validated as non-empty string
- **No Sensitive Data**: Only measurement ID is exposed in output

### Best Practices

1. **Never Commit Sensitive Data**:

```bash
# Add to .gitignore
.env
.env.local
.env.*.local
```

2. **Use Different IDs Per Environment**:

```env
# Development (optional - can be empty)
GA4_MEASUREMENT_ID=

# Staging
GA4_MEASUREMENT_ID=G-STG123456

# Production
GA4_MEASUREMENT_ID=G-PRO123456
```

3. **Regular Security Updates**:

```bash
# Check for vulnerabilities
composer audit

# Update dependencies
composer update
```

## 🧪 Testing

### Running Tests

```bash
# Run all tests
composer test

# Generate HTML coverage report
composer test-coverage

# Run tests with verbose output
composer test-verbose

# Run specific test method(s)
composer test-filter testRenderReturnsEmptyStringWhenNotConfigured

# Static analysis with PHPStan
composer analyse

# Check code style (dry run)
composer lint

# Fix code style issues
composer pint
```

### Test Coverage

The package maintains **100% test coverage** with:

- **101 tests** (8 test files)
- **229 assertions**
- Comprehensive edge case coverage
- Configuration validation tests
- All tests passing with PHP 8.2-8.5 compatibility

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

# Generate coverage report (HTML)
composer test-coverage

# Run tests with verbose output
composer test-verbose

# Fix code style issues with Pint
composer pint

# Static analysis with PHPStan
composer analyse
```

## 📦 Dependencies

### Required Dependencies

- **PHP**: ^8.2 (8.2 or higher)
- **illuminate/support**: ^10.0|^11.0|^12.0 (Laravel 10-12 compatible)

### Development Dependencies

- **orchestra/testbench**: ^8.0|^9.0|^10.9 (Laravel package testing)
- **phpunit/phpunit**: ^10.0|^11.5 (testing framework)
- **laravel/pint**: ^1.26 (code style fixing)
- **phpstan/phpstan**: ^2.1.33 (static analysis)

### Suggested Dependencies

- **None**: Package works standalone without any optional dependencies

## 📄 License

This package is open-sourced software licensed under the [MIT license](LICENSE).

## 🤝 Support

### Getting Help

- **GitHub Issues**: [Report bugs or request features](https://github.com/ronald2wing/laravel-ga4/issues)
- **GitHub Discussions**: [Ask questions and share ideas](https://github.com/ronald2wing/laravel-ga4/discussions)
- **Documentation**: This README and source code comments

### Contributing

Contributions are welcome! Please:

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Add tests for new functionality
5. Ensure all tests pass
6. Submit a pull request

---

**Made with ❤️ by [Ronald2Wing](https://github.com/ronald2wing)**

If you find this package useful, please consider:

- ⭐ **Starring the repository** on GitHub
- 🐛 **Reporting issues** to help improve the package
- 💖 **Sponsoring the developer** on [GitHub Sponsors](https://github.com/sponsors/ronald2wing)
