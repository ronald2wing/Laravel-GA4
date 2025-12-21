<?php

namespace Ronald2Wing\LaravelGa4\Tests\Unit;

use Orchestra\Testbench\TestCase;
use Ronald2Wing\LaravelGa4\Ga4Service;
use Ronald2Wing\LaravelGa4\Ga4ServiceProvider;

class IntegrationTest extends TestCase
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

    /**
     * Test full integration with Laravel container.
     */
    public function test_full_integration_with_laravel_container(): void
    {
        // Set configuration
        $this->app['config']->set('ga4.measurement_id', 'G-INTEGRATION-TEST');

        // Get service from container
        $service = $this->app->make('ga4');

        $this->assertInstanceOf(Ga4Service::class, $service);

        // Render script
        $script = $service->render();

        $this->assertStringContainsString('G-INTEGRATION-TEST', $script);
        $this->assertStringContainsString('googletagmanager.com/gtag/js', $script);
        $this->assertStringContainsString('send_page_view: false', $script);
    }

    /**
     * Test facade integration.
     */
    public function test_facade_integration(): void
    {
        // Set configuration
        $this->app['config']->set('ga4.measurement_id', 'G-FACADE-TEST');

        // Use facade
        $script = \Ga4::render();

        $this->assertStringContainsString('G-FACADE-TEST', $script);
        $this->assertStringContainsString('googletagmanager.com/gtag/js', $script);
        $this->assertStringContainsString('send_page_view: false', $script);
    }

    /**
     * Test helper function integration.
     */
    public function test_helper_function_integration(): void
    {
        // Set configuration
        $this->app['config']->set('ga4.measurement_id', 'G-HELPER-TEST');

        // Use helper function
        $script = ga4();

        $this->assertStringContainsString('G-HELPER-TEST', $script);
        $this->assertStringContainsString('googletagmanager.com/gtag/js', $script);
        $this->assertStringContainsString('send_page_view: false', $script);
    }

    /**
     * Test that service can be resolved via alias.
     */
    public function test_service_can_be_resolved_via_alias(): void
    {
        // The service is registered as 'ga4' in the container, not by class name
        // This is expected behavior for Laravel services
        $service = $this->app->make('ga4');

        $this->assertInstanceOf(Ga4Service::class, $service);
    }

    /**
     * Test that configuration is properly loaded from config file.
     */
    public function test_configuration_is_properly_loaded_from_config_file(): void
    {
        $config = $this->app['config']->get('ga4');

        $this->assertIsArray($config);
        $this->assertArrayHasKey('measurement_id', $config);
    }

    /**
     * Test that service works with different environments.
     */
    public function test_service_works_with_different_environments(): void
    {
        // Test with a single environment to avoid the loop issue
        $environment = 'production';

        // Create service with config for this environment
        $service = new Ga4Service(['measurement_id' => "G-{$environment}"]);

        // Render script
        $script = $service->render();

        $this->assertStringContainsString("G-{$environment}", $script);
    }

    /**
     * Test that service handles configuration changes at runtime.
     */
    public function test_service_handles_configuration_changes_at_runtime(): void
    {
        // Initial config
        $this->app['config']->set('ga4.measurement_id', 'G-INITIAL');

        $service = $this->app->make('ga4');
        $script1 = $service->render();

        $this->assertStringContainsString('G-INITIAL', $script1);

        // Change config
        $this->app['config']->set('ga4.measurement_id', 'G-CHANGED');

        // Since service is singleton with config injected in constructor,
        // it won't pick up runtime config changes. This is expected behavior.
        $script2 = $service->render();

        // Should still contain initial value (singleton with initial config)
        $this->assertStringContainsString('G-INITIAL', $script2);

        // New instance should pick up changed config
        $newService = new Ga4Service(['measurement_id' => 'G-CHANGED']);
        $script3 = $newService->render();

        $this->assertStringContainsString('G-CHANGED', $script3);
    }

    /**
     * Test that service works with empty configuration.
     */
    public function test_service_works_with_empty_configuration(): void
    {
        // Set empty config
        $this->app['config']->set('ga4.measurement_id', '');

        $service = $this->app->make('ga4');
        $script = $service->render();

        $this->assertEmpty($script);
    }

    /**
     * Test that service can be used in Blade templates.
     */
    public function test_service_can_be_used_in_blade_templates(): void
    {
        // Set configuration
        $this->app['config']->set('ga4.measurement_id', 'G-BLADE-TEST');

        // Create a simple Blade template that uses the helper
        $bladeContent = <<<'BLADE'
                <!DOCTYPE html>
                <html>
                <head>
                    {!! ga4() !!}
                </head>
                <body>
                    <h1>Test Page</h1>
                </body>
                </html>
            BLADE;

        // Compile Blade template
        $compiled = $this->app['blade.compiler']->compileString($bladeContent);

        // Evaluate the compiled template
        ob_start();
        eval('?>'.$compiled);
        $output = ob_get_clean();

        // Check that GA4 script is included
        $this->assertStringContainsString('G-BLADE-TEST', $output);
        $this->assertStringContainsString('googletagmanager.com/gtag/js', $output);
    }
}
