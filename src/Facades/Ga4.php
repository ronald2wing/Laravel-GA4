<?php

namespace Ronald2Wing\LaravelGa4\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * Facade for the Google Analytics 4 (GA4) tracking service.
 *
 * Provides easy access to GA4 tracking functionality throughout your Laravel application.
 *
 * **Usage Examples:**
 * ```php
 * // In controllers or services
 * $ga4Script = Ga4::render();
 *
 * // In Blade templates (prefer the helper function)
 * {!! Ga4::render() !!}
 * ```
 *
 * **Available Methods:**
 *
 * @method static string render() Render GA4 tracking script with automatic Livewire detection.
 *                                Returns empty string if measurement ID is not configured.
 *
 * **Features:**
 * - Automatic Livewire SPA navigation detection
 * - Returns empty string when not configured
 * - Proper HTML escaping for security
 * - Singleton service registration
 *
 * **Service Registration:**
 * The facade resolves to the 'ga4' service registered in the service container.
 *
 * @see \Ronald2Wing\LaravelGa4\Ga4Service
 * @see \Ronald2Wing\LaravelGa4\Ga4ServiceProvider
 */
class Ga4 extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * Returns the service name used for dependency injection.
     * This matches the service registration in Ga4ServiceProvider.
     *
     * @return string Service name used for dependency injection
     */
    protected static function getFacadeAccessor(): string
    {
        return 'ga4';
    }
}
