<?php

namespace Ronald2Wing\LaravelGa4\Tests\Unit;

use Orchestra\Testbench\TestCase;
use Ronald2Wing\LaravelGa4\Ga4ServiceProvider;
use Ronald2Wing\LaravelGa4\Tests\Traits\CreatesFakeLivewireClass;

class HelperFunctionTest extends TestCase
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
     * Test that helper function exists.
     */
    public function test_helper_function_exists(): void
    {
        $this->assertTrue(function_exists('ga4'));
    }

    /**
     * Test that helper function renders script correctly.
     */
    public function test_helper_function_renders_script_correctly(): void
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
     * Test that helper function returns empty string when measurement ID is empty.
     */
    public function test_helper_function_returns_empty_string_when_measurement_id_is_empty(): void
    {
        // Set empty configuration
        $this->app['config']->set('ga4.measurement_id', '');

        // Use helper function
        $script = ga4();

        $this->assertEmpty($script);
    }

    /**
     * Test that helper function works with different measurement IDs.
     */
    public function test_helper_function_works_with_different_measurement_ids(): void
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

            // Use helper function
            $script = ga4();

            $this->assertStringContainsString($measurementId, $script);
            $this->assertStringContainsString('googletagmanager.com/gtag/js', $script);
        }
    }

    /**
     * Test that helper function can be used in Blade templates.
     */
    public function test_helper_function_can_be_used_in_blade_templates(): void
    {
        // Set configuration
        $this->app['config']->set('ga4.measurement_id', 'G-BLADE-HELPER');

        // Create a simple Blade template that uses the helper function
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
        $this->assertStringContainsString('G-BLADE-HELPER', $output);
        $this->assertStringContainsString('googletagmanager.com/gtag/js', $output);
    }

    /**
     * Test that helper function returns same result as facade.
     */
    public function test_helper_function_returns_same_result_as_facade(): void
    {
        // Set configuration
        $this->app['config']->set('ga4.measurement_id', 'G-COMPARE-TEST');

        // Get result from helper function
        $helperResult = ga4();

        // Get result from facade
        $facadeResult = \Ga4::render();

        $this->assertEquals($helperResult, $facadeResult);
        $this->assertStringContainsString('G-COMPARE-TEST', $helperResult);
        $this->assertStringContainsString('G-COMPARE-TEST', $facadeResult);
    }

    /**
     * Test that helper function works with Livewire integration.
     */
    /**
     * @runInSeparateProcess
     *
     * @preserveGlobalState disabled
     */
    public function test_helper_function_works_with_livewire_integration(): void
    {
        // Create a fake Livewire class
        $this->createFakeLivewireClass();

        // Set configuration
        $this->app['config']->set('ga4.measurement_id', 'G-LIVEWIRE-HELPER');

        // Clear the cached service instance so it gets recreated with new config
        $this->app->forgetInstance('ga4');
        \Ronald2Wing\LaravelGa4\Facades\Ga4::clearResolvedInstance('ga4');

        // Use helper function
        $script = ga4();

        $this->assertStringContainsString('G-LIVEWIRE-HELPER', $script);
        $this->assertStringContainsString('livewire:navigated', $script);
        $this->assertStringContainsString('document.addEventListener', $script);
    }

    /**
     * Test that helper function is only defined once.
     */
    public function test_helper_function_is_only_defined_once(): void
    {
        // Try to require the helpers file again
        require_once __DIR__.'/../../src/helpers.php';

        // The function should still exist without errors
        $this->assertTrue(function_exists('ga4'));
    }

    /**
     * Test that helper function handles edge cases.
     */
    public function test_helper_function_handles_edge_cases(): void
    {
        // Test with whitespace-only measurement ID
        $this->app['config']->set('ga4.measurement_id', '   ');
        $this->app->forgetInstance('ga4');
        \Ronald2Wing\LaravelGa4\Facades\Ga4::clearResolvedInstance('ga4');
        $script = ga4();
        $this->assertEmpty($script);

        // Test with very long measurement ID
        $longId = 'G-'.str_repeat('A', 100);
        $this->app['config']->set('ga4.measurement_id', $longId);
        $this->app->forgetInstance('ga4');
        \Ronald2Wing\LaravelGa4\Facades\Ga4::clearResolvedInstance('ga4');
        $script = ga4();
        $this->assertStringContainsString($longId, $script);

        // Test with special characters
        $specialId = 'G-TEST-123_ABC@test';
        $this->app['config']->set('ga4.measurement_id', $specialId);
        $this->app->forgetInstance('ga4');
        \Ronald2Wing\LaravelGa4\Facades\Ga4::clearResolvedInstance('ga4');
        $script = ga4();
        $this->assertStringContainsString($specialId, $script);
    }
}
