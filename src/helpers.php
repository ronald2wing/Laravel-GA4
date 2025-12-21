<?php

use Ronald2Wing\LaravelGa4\Facades\Ga4;

if (! function_exists('ga4')) {
    /**
     * Render Google Analytics 4 (GA4) tracking script HTML.
     *
     * This helper provides easy access to GA4 tracking in Blade templates.
     * The script includes automatic Livewire SPA navigation detection when Livewire is installed.
     *
     * **Usage Examples:**
     * ```blade
     * <!-- In your Blade layout file -->
     * <body>
     *     <!-- Your content -->
     *     {!! ga4() !!}
     * </body>
     * ```
     *
     * ```blade
     * <!-- In any Blade template -->
     * {!! ga4() !!}
     * ```
     *
     * **Features:**
     * - Returns empty string if measurement ID is not configured
     * - Automatically detects Livewire for SPA navigation tracking
     * - Proper HTML escaping for security
     * - Disables auto page_view for better control
     *
     * **Return Value:**
     * - string: GA4 tracking script HTML or empty string if not configured
     *
     * @return string GA4 tracking script HTML or empty string if not configured
     *
     * @see \Ronald2Wing\LaravelGa4\Ga4Service::render()
     */
    function ga4(): string
    {
        return Ga4::render();
    }
}
