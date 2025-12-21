<?php

namespace Ronald2Wing\LaravelGa4\Tests\Unit;

use Orchestra\Testbench\TestCase;
use Ronald2Wing\LaravelGa4\Ga4Service;
use Ronald2Wing\LaravelGa4\Ga4ServiceProvider;
use Ronald2Wing\LaravelGa4\Tests\Traits\CreatesFakeLivewireClass;

class LivewireIntegrationTest extends TestCase
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
     * Test that script doesn't include Livewire integration when Livewire is not installed.
     */
    public function test_script_excludes_livewire_integration_when_not_installed(): void
    {
        $ga4 = new Ga4Service(['measurement_id' => 'G-TEST-LW']);

        $script = $ga4->render();

        $this->assertStringNotContainsString('livewire:navigated', $script);
        $this->assertStringNotContainsString("document.addEventListener('livewire:navigated'", $script);
    }

    /**
     * Test that Livewire integration script is included when Livewire is installed.
     *
     * @runInSeparateProcess
     *
     * @preserveGlobalState disabled
     */
    public function test_livewire_integration_script_included_when_livewire_installed(): void
    {
        $this->createFakeLivewireClass();
        $ga4 = new Ga4Service(['measurement_id' => 'G-TEST-LW']);

        $script = $ga4->render();

        $this->assertStringContainsString('livewire:navigated', $script);
        $this->assertStringContainsString('googletagmanager.com/gtag/js', $script);
        $this->assertStringContainsString('window.dataLayer', $script);
        $this->assertStringContainsString('document.addEventListener', $script);
    }

    /**
     * Test that Livewire integration script has correct structure.
     *
     * @runInSeparateProcess
     *
     * @preserveGlobalState disabled
     */
    public function test_livewire_integration_script_has_correct_structure(): void
    {
        $this->createFakeLivewireClass();
        $ga4 = new Ga4Service(['measurement_id' => 'G-TEST-LW']);

        $script = $ga4->render();

        // Check for Livewire event listener
        $this->assertStringContainsString('document.addEventListener', $script);
        $this->assertStringContainsString('gtag(\'config\', "G-TEST-LW"', $script);
        $this->assertStringContainsString('send_page_view: false', $script);

        // Should contain the measurement ID
        $this->assertStringContainsString('G-TEST-LW', $script);

        // Should contain the Google Analytics script tag
        $this->assertStringContainsString('googletagmanager.com/gtag/js', $script);
        $this->assertStringContainsString('<script async', $script);
        $this->assertStringContainsString('</script>', $script);
    }

    /**
     * Test that Livewire integration works with different measurement IDs.
     *
     * @runInSeparateProcess
     *
     * @preserveGlobalState disabled
     */
    public function test_livewire_integration_works_with_different_measurement_ids(): void
    {
        $measurementIds = [
            'G-1234567890',
            'G-ABC123DEF4',
            'G-TEST-123-ABC',
            'G-123_ABC_456',
        ];

        foreach ($measurementIds as $measurementId) {
            $this->createFakeLivewireClass();
            $ga4 = new Ga4Service(['measurement_id' => $measurementId]);

            $script = $ga4->render();

            $this->assertStringContainsString($measurementId, $script);
            $this->assertStringContainsString('livewire:navigated', $script);
            $this->assertStringContainsString('document.addEventListener(\'livewire:navigated\'', $script);
        }
    }

    /**
     * Test that Livewire integration is not included when measurement ID is empty.
     *
     * @runInSeparateProcess
     *
     * @preserveGlobalState disabled
     */
    public function test_livewire_integration_not_included_when_measurement_id_is_empty(): void
    {
        $this->createFakeLivewireClass();
        $ga4 = new Ga4Service(['measurement_id' => '']);

        $script = $ga4->render();

        $this->assertEmpty($script);
        $this->assertStringNotContainsString('livewire:navigated', $script);
        $this->assertStringNotContainsString("document.addEventListener('livewire:navigated'", $script);
    }
}
