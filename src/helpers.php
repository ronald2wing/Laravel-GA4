<?php

if (! function_exists('ga4')) {
    /**
     * Render Google Analytics 4 (GA4) tracking script HTML.
     *
     * This helper provides easy access to GA4 tracking in Blade templates.
     *
     * **Usage Examples:**
     * ```blade
     * <!-- In your Blade layout file -->
     * <head>
     *     <!-- Your meta tags -->
     *     {!! ga4() !!}
     * </head>
     * ```
     *
     * ```blade
     * <!-- In any Blade template -->
     * {!! ga4() !!}
     * ```
     *
     * **Features:**
     * - Returns empty string if measurement ID is not configured
     * - Proper HTML escaping for security
     * - Environment-aware rendering
     *
     * **Return Value:**
     * - string: GA4 tracking script HTML or empty string if not configured
     *
     * @return string GA4 tracking script HTML or empty string if not configured
     */
    function ga4(): string
    {
        return app('ga4')->render();
    }
}
