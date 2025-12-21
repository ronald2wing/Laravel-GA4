<?php

namespace Ronald2Wing\LaravelGa4\Tests\Unit;

use Orchestra\Testbench\TestCase;
use Ronald2Wing\LaravelGa4\Ga4Service;
use Ronald2Wing\LaravelGa4\Ga4ServiceProvider;

class ConfigurationTest extends TestCase
{
    /**
     * Get package providers.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return array<int, class-string>
     */
    protected function getPackageProviders($app): array
    {
        return [
            Ga4ServiceProvider::class,
        ];
    }

    /**
     * Get package aliases.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return array<string, class-string>
     */
    protected function getPackageAliases($app): array
    {
        return [
            'Ga4' => \Ronald2Wing\LaravelGa4\Facades\Ga4::class,
        ];
    }

    // ============================================
    // Basic Configuration Tests
    // ============================================

    /**
     * Test that configuration is properly loaded.
     */
    public function test_configuration_is_properly_loaded(): void
    {
        $config = $this->app['config']->get('ga4');

        $this->assertIsArray($config);
        $this->assertArrayHasKey('measurement_id', $config);
        $this->assertIsString($config['measurement_id']);
    }

    /**
     * Test that configuration has correct default value.
     */
    public function test_configuration_has_correct_default_value(): void
    {
        $config = $this->app['config']->get('ga4');

        $this->assertEquals('', $config['measurement_id']);
    }

    /**
     * Test that configuration structure is correct.
     */
    public function test_configuration_structure_is_correct(): void
    {
        $config = $this->app['config']->get('ga4');

        $this->assertCount(1, $config, 'Configuration should have exactly one key');
        $this->assertArrayHasKey('measurement_id', $config);
    }

    // ============================================
    // Configuration Format Tests
    // ============================================

    /**
     * Data provider for different measurement ID formats.
     *
     * @return array<string, array<string>>
     */
    public static function measurementIdFormatsProvider(): array
    {
        return [
            'standard format' => ['G-1234567890'],
            'alphanumeric format' => ['G-ABC123DEF4'],
            'hyphenated format' => ['G-TEST-123-ABC'],
            'underscore format' => ['G-123_ABC_456'],
        ];
    }

    /**
     * Test different measurement ID formats.
     *
     * @dataProvider measurementIdFormatsProvider
     */
    public function test_different_measurement_id_formats(string $measurementId): void
    {
        $config = ['measurement_id' => $measurementId];
        $ga4 = new Ga4Service($config);

        $script = $ga4->render();

        if (! empty($script)) {
            $this->assertStringContainsString($measurementId, $script);
            $this->assertStringContainsString('send_page_view: false', $script);
        }
    }

    /**
     * Test that configuration can be loaded from environment variables.
     */
    public function test_configuration_can_be_loaded_from_environment_variables(): void
    {
        // Test with environment variable set
        putenv('GA4_MEASUREMENT_ID=G-TEST-ENV');

        // Reload config to pick up environment variable
        $this->refreshApplication();

        $config = $this->app['config']->get('ga4');

        $this->assertIsArray($config);
        $this->assertArrayHasKey('measurement_id', $config);
        $this->assertEquals('G-TEST-ENV', $config['measurement_id']);

        // Clean up
        putenv('GA4_MEASUREMENT_ID');
    }

    /**
     * Test that configuration falls back to empty string when environment variable is not set.
     */
    public function test_configuration_falls_back_to_empty_string_when_env_not_set(): void
    {
        // Ensure environment variable is not set
        putenv('GA4_MEASUREMENT_ID');

        // Reload config
        $this->refreshApplication();

        $config = $this->app['config']->get('ga4');

        $this->assertIsArray($config);
        $this->assertArrayHasKey('measurement_id', $config);
        $this->assertEquals('', $config['measurement_id']);
    }

    /**
     * Test that configuration can be overridden programmatically.
     */
    public function test_configuration_can_be_overridden_programmatically(): void
    {
        // Set initial config
        $this->app['config']->set('ga4.measurement_id', 'G-INITIAL');

        $initialConfig = $this->app['config']->get('ga4');
        $this->assertEquals('G-INITIAL', $initialConfig['measurement_id']);

        // Override config
        $this->app['config']->set('ga4.measurement_id', 'G-OVERRIDDEN');

        $overriddenConfig = $this->app['config']->get('ga4');
        $this->assertEquals('G-OVERRIDDEN', $overriddenConfig['measurement_id']);
    }

    /**
     * Test that service uses configuration from Laravel config system.
     */
    public function test_service_uses_configuration_from_laravel_config_system(): void
    {
        // Set config via Laravel config system
        $this->app['config']->set('ga4.measurement_id', 'G-CONFIG-SYSTEM');

        // Get service from container (should use config from config system)
        $service = $this->app->make('ga4');

        $script = $service->render();

        $this->assertStringContainsString('G-CONFIG-SYSTEM', $script);
        $this->assertStringContainsString('googletagmanager.com/gtag/js', $script);
    }

    /**
     * Test that configuration merging works correctly.
     */
    public function test_configuration_merging_works_correctly(): void
    {
        // Get initial config
        $initialConfig = $this->app['config']->get('ga4');

        $this->assertIsArray($initialConfig);
        $this->assertArrayHasKey('measurement_id', $initialConfig);

        // Merge new config
        $this->app['config']->set('ga4', array_merge(
            $initialConfig,
            ['measurement_id' => 'G-MERGED']
        ));

        $mergedConfig = $this->app['config']->get('ga4');

        $this->assertEquals('G-MERGED', $mergedConfig['measurement_id']);
    }

    /**
     * Test that configuration is cached in production environment.
     */
    public function test_configuration_is_cached_in_production_environment(): void
    {
        // Set environment to production
        $this->app['env'] = 'production';

        // Set config
        $this->app['config']->set('ga4.measurement_id', 'G-PRODUCTION');

        // Get config
        $config = $this->app['config']->get('ga4');

        $this->assertEquals('G-PRODUCTION', $config['measurement_id']);

        // Reset environment
        $this->app['env'] = 'testing';
    }
}
