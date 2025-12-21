# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.0] - 2025-12-24

### 🎉 Initial Release

First stable release of the Laravel GA4 package.

### ✨ Added

- Core GA4 Service (`Ga4Service`) for generating GA4 tracking scripts
- Automatic Livewire integration detection for SPA navigation tracking
- Laravel service provider for package registration and configuration
- `Ga4` facade for programmatic access
- `ga4()` helper function for Blade template integration
- Publishable configuration file (`config/ga4.php`)
- Support for `GA4_MEASUREMENT_ID` environment variable
- Proper HTML escaping and JavaScript injection safety

### 🔧 Features

- Automatic script injection: `{!! ga4() !!}`
- Livewire SPA detection and tracking
- Environment-aware rendering (only when measurement ID is configured)
- Disabled auto page_view for better control
- Configurable via publishable configuration
- Comprehensive error handling

### 🧪 Testing

- 100% test coverage
- 119 tests, 288 assertions
- Livewire integration tests
- Configuration and edge case tests

### 📦 Dependencies

- PHP: ^8.2
- Laravel: ^10.x|^11.x|^12.x
- illuminate/support: ^10.0|^11.0|^12.0

[1.0.0]: https://github.com/ronald2wing/laravel-ga4/releases/tag/v1.0.0
