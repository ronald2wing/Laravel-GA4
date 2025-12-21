<?php

namespace Ronald2Wing\LaravelGa4\Tests\Unit;

use Orchestra\Testbench\TestCase;
use Ronald2Wing\LaravelGa4\Facades\Ga4;
use Ronald2Wing\LaravelGa4\Ga4ServiceProvider;
use Ronald2Wing\LaravelGa4\Tests\Traits\CreatesFakeLivewireClass;

class FacadeTest extends TestCase
{
    use CreatesFakeLivewireClass;

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
     * Test that facade can be accessed.
     */
    public function test_facade_can_be_accessed(): void
    {
        $this->assertTrue(class_exists('Ga4'));
    }

    /**
     * Test that facade renders script correctly.
     */
    public function test_facade_renders_script_correctly(): void
    {
        // Set configuration
        $this->app['config']->set('ga4.measurement_id', 'G-FACADE-TEST');

        // Use facade
        $script = Ga4::render();

        $this->assertStringContainsString('G-FACADE-TEST', $script);
        $this->assertStringContainsString('googletagmanager.com/gtag/js', $script);
        $this->assertStringContainsString('send_page_view: false', $script);
    }

    /**
     * Test that facade returns empty string when measurement ID is empty.
     */
    public function test_facade_returns_empty_string_when_measurement_id_is_empty(): void
    {
        // Set empty configuration
        $this->app['config']->set('ga4.measurement_id', '');

        // Use facade
        $script = Ga4::render();

        $this->assertEmpty($script);
    }

    /**
     * Test that facade works with different measurement IDs.
     */
    public function test_facade_works_with_different_measurement_ids(): void
    {
        $measurementIds = [
            'G-1234567890',
            'G-ABC123DEF4',
            'G-TEST-123-ABC',
            'G-123_ABC_456',
        ];

        foreach ($measurementIds as $measurementId) {
            // Set configuration
            $this->app['config']->set('ga4.measurement_id', $measurementId);

            // Clear the cached service instance so it gets recreated with new config
            $this->app->forgetInstance('ga4');
            \Ronald2Wing\LaravelGa4\Facades\Ga4::clearResolvedInstance('ga4');

            // Use facade
            $script = Ga4::render();

            $this->assertStringContainsString($measurementId, $script);
            $this->assertStringContainsString('googletagmanager.com/gtag/js', $script);
        }
    }

    /**
     * Test that facade is registered as an alias.
     */
    public function test_facade_is_registered_as_alias(): void
    {
        // Check that facade alias is registered in the application
        // We can check by trying to resolve it
        $facade = \Ga4::getFacadeRoot();
        $this->assertInstanceOf(\Ronald2Wing\LaravelGa4\Ga4Service::class, $facade);
    }

    /**
     * Test that facade resolves to Ga4Service instance.
     */
    public function test_facade_resolves_to_ga4_service_instance(): void
    {
        $service = $this->app->make('ga4');

        $this->assertInstanceOf(\Ronald2Wing\LaravelGa4\Ga4Service::class, $service);
    }

    /**
     * Test that facade can be used in Blade templates.
     */
    public function test_facade_can_be_used_in_blade_templates(): void
    {
        // Set configuration
        $this->app['config']->set('ga4.measurement_id', 'G-BLADE-FACADE');

        // Create a simple Blade template that uses the facade
        $bladeContent = <<<'BLADE'
            <!DOCTYPE html>
            <html>
            <head>
                {!! Ga4::render() !!}
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
        $this->assertStringContainsString('G-BLADE-FACADE', $output);
        $this->assertStringContainsString('googletagmanager.com/gtag/js', $output);
    }

    /**
     * Test that facade methods are accessible.
     */
    public function test_facade_methods_are_accessible(): void
    {
        // Set configuration
        $this->app['config']->set('ga4.measurement_id', 'G-TEST-METHODS');

        // Clear any cached instance
        $this->app->forgetInstance('ga4');

        // The facade should be able to call the render method
        $script = Ga4::render();

        // If we get here without error, the method is accessible
        $this->assertIsString($script);
    }

    /**
     * Test that facade root is Ga4Service.
     */
    public function test_facade_root_is_ga4_service(): void
    {
        $root = Ga4::getFacadeRoot();

        $this->assertInstanceOf(\Ronald2Wing\LaravelGa4\Ga4Service::class, $root);
    }

    /**
     * Test that facade works with Livewire integration.
     *
     * @runInSeparateProcess
     *
     * @preserveGlobalState disabled
     */
    public function test_facade_works_with_livewire_integration(): void
    {
        // Create a fake Livewire class
        $this->createFakeLivewireClass();

        // Set configuration
        $this->app['config']->set('ga4.measurement_id', 'G-LIVEWIRE-FACADE');

        // Clear the cached service instance so it gets recreated with new config
        $this->app->forgetInstance('ga4');
        \Ronald2Wing\LaravelGa4\Facades\Ga4::clearResolvedInstance('ga4');

        // Use facade
        $script = Ga4::render();

        $this->assertStringContainsString('G-LIVEWIRE-FACADE', $script);
        $this->assertStringContainsString('livewire:navigated', $script);
        $this->assertStringContainsString('document.addEventListener', $script);
    }
}
