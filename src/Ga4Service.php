<?php

namespace Ronald2Wing\LaravelGa4;

/**
 * Google Analytics 4 (GA4) tracking service for Laravel.
 *
 * This service handles GA4 script generation for Google Analytics 4 tracking.
 *
 * @see https://developers.google.com/analytics/devguides/collection/ga4
 */
class Ga4Service
{
    /** @var array<string, mixed> Service configuration. */
    private array $config;

    /** @var string|null Valid measurement ID to track. */
    private ?string $measurementId = null;

    /** @var bool Whether tracking is enabled. */
    private bool $enabled = true;

    /**
     * Create a new GA4 service instance.
     *
     * @param  array<string, mixed>  $config  Service configuration
     */
    public function __construct(array $config)
    {
        $this->config = $config;
        $this->initialize();
    }

    /**
     * Initialize the service with configuration.
     */
    private function initialize(): void
    {
        $this->measurementId = $this->extractValidMeasurementId();
        $this->enabled = $this->measurementId !== null;
    }

    /**
     * Extract and validate measurement ID from configuration.
     *
     * @return string|null Valid measurement ID or null if invalid
     */
    private function extractValidMeasurementId(): ?string
    {
        $measurementId = $this->config['measurement_id'] ?? '';

        if (! is_string($measurementId)) {
            return null;
        }

        $trimmedId = trim($measurementId);

        return $this->isValidMeasurementId($trimmedId) ? $trimmedId : null;
    }

    /**
     * Validate GA4 measurement ID format.
     *
     * @param  string  $measurementId  Measurement ID to validate
     * @return bool True if valid GA4 measurement ID
     */
    private function isValidMeasurementId(string $measurementId): bool
    {
        if (empty($measurementId)) {
            return false;
        }

        // GA4 measurement ID format: G-XXXXXXXXXX
        // Accept any non-empty string that starts with G-
        return str_starts_with($measurementId, 'G-') && strlen($measurementId) > 2;
    }

    /**
     * Check if GA4 tracking should be rendered.
     *
     * Returns true when tracking is enabled and valid measurement ID exists.
     *
     * @return bool True if tracking should be rendered
     */
    private function shouldRenderTracking(): bool
    {
        return $this->enabled && $this->measurementId !== null;
    }

    /**
     * Generate core GA4 initialization JavaScript.
     *
     * Creates the JavaScript code that initializes Google Analytics 4 tracking.
     * Includes dataLayer initialization, gtag function definition, and configuration.
     *
     * @return string Core GA4 JavaScript code
     */
    private function generateCoreScript(): string
    {
        // Generate JavaScript
        $script = "window.dataLayer = window.dataLayer || [];\n";
        $script .= "function gtag(){dataLayer.push(arguments);}\n";
        $script .= "gtag('js', new Date());\n";

        $escapedId = json_encode(
            $this->measurementId,
            JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP
        );
        $script .= "gtag('config', {$escapedId});\n";

        return $script;
    }

    /**
     * Render GA4 tracking script HTML.
     *
     * Returns complete HTML script tag with GA4 tracking code.
     * The method includes security measures like HTML escaping and JSON encoding
     * with security flags to prevent XSS attacks.
     *
     * @return string GA4 tracking script HTML or empty string if not configured
     */
    public function render(): string
    {
        if (! $this->shouldRenderTracking()) {
            return '';
        }

        $escapedId = htmlspecialchars($this->measurementId, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $html = "<script async src=\"https://www.googletagmanager.com/gtag/js?id={$escapedId}\"></script>\n";

        // Add main script
        $javaScript = $this->generateCoreScript();
        $html .= "<script>\n{$javaScript}\n</script>";

        return $html;
    }
}
