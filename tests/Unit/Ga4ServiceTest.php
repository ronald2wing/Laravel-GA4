<?php

namespace Ronald2Wing\LaravelGa4\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Ronald2Wing\LaravelGa4\Ga4Service;

class Ga4ServiceTest extends TestCase
{
    /** @var array<string, string> Valid configuration with measurement ID for testing. */
    private array $validConfig;

    /** @var array<string, string> Empty configuration (no measurement ID). */
    private array $emptyConfig;

    /**
     * Set up test configurations.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->validConfig = [
            'measurement_id' => 'G-TEST123',
        ];

        $this->emptyConfig = [
            'measurement_id' => '',
        ];
    }

    // ============================================
    // Basic Rendering Tests
    // ============================================

    /**
     * Test that service renders empty string when measurement ID is not configured.
     */
    public function test_renders_empty_string_when_measurement_id_is_empty(): void
    {
        $ga4 = new Ga4Service($this->emptyConfig);

        $this->assertEmpty($ga4->render());
    }

    /**
     * Test that service renders script correctly with measurement ID.
     */
    public function test_renders_script_correctly_with_measurement_id(): void
    {
        $ga4 = new Ga4Service($this->validConfig);

        $script = $ga4->render();

        $this->assertStringContainsString('G-TEST123', $script);
        $this->assertStringContainsString('googletagmanager.com/gtag/js', $script);
        $this->assertStringContainsString('window.dataLayer', $script);
        // Should NOT contain send_page_view: false when Livewire is not installed
        $this->assertStringNotContainsString('send_page_view: false', $script);
        // Should not contain Livewire code by default in tests (Livewire not installed)
        $this->assertStringNotContainsString('livewire:navigated', $script);
    }

    // ============================================
    // Invalid Measurement ID Tests
    // ============================================

    /**
     * Data provider for invalid measurement ID types.
     *
     * @return array<string, array<mixed>>
     */
    public static function invalidMeasurementIdProvider(): array
    {
        return [
            'null' => [null],
            'boolean false' => [false],
            'empty array' => [[]],
            'object' => [new \stdClass],
        ];
    }

    /**
     * Test that service returns empty string for invalid measurement ID types.
     *
     * @dataProvider invalidMeasurementIdProvider
     */
    public function test_renders_empty_string_for_invalid_measurement_id_types(mixed $invalidMeasurementId): void
    {
        $config = ['measurement_id' => $invalidMeasurementId];
        $ga4 = new Ga4Service($config);

        $this->assertEmpty($ga4->render());
    }

    // ============================================
    // Configuration Edge Cases
    // ============================================

    /**
     * Test that service trims whitespace from measurement ID.
     */
    public function test_trims_whitespace_from_measurement_id(): void
    {
        $config = ['measurement_id' => '  G-TEST123  '];
        $ga4 = new Ga4Service($config);

        $script = $ga4->render();

        $this->assertStringContainsString('G-TEST123', $script);
        $this->assertStringNotContainsString('  G-TEST123  ', $script);
    }

    /**
     * Test that service handles very long measurement IDs.
     */
    public function test_handles_very_long_measurement_ids(): void
    {
        $longId = 'G-'.str_repeat('A', 100);
        $config = ['measurement_id' => $longId];
        $ga4 = new Ga4Service($config);

        $script = $ga4->render();

        $this->assertStringContainsString($longId, $script);
        $this->assertStringContainsString('googletagmanager.com/gtag/js', $script);
    }

    /**
     * Data provider for invalid configuration cases.
     *
     * @return array<string, array<mixed>>
     */
    public static function invalidConfigurationProvider(): array
    {
        return [
            'empty config array' => [[]],
            'config without measurement_id key' => [['other_key' => 'value']],
        ];
    }

    /**
     * Test that service returns empty string for invalid configurations.
     *
     * @dataProvider invalidConfigurationProvider
     */
    public function test_renders_empty_string_for_invalid_configurations(array $invalidConfig): void
    {
        $ga4 = new Ga4Service($invalidConfig);

        $this->assertEmpty($ga4->render());
    }

    // ============================================
    // Security Tests
    // ============================================

    /**
     * Test that HTML output is properly escaped.
     */
    public function test_html_output_is_properly_escaped(): void
    {
        $dangerousId = 'G-TEST"><script>alert("xss")</script>';
        $ga4 = new Ga4Service(['measurement_id' => $dangerousId]);

        $script = $ga4->render();

        // Should contain escaped version in URL
        $this->assertStringContainsString(
            'G-TEST&quot;&gt;&lt;script&gt;alert(&quot;xss&quot;)&lt;/script&gt;',
            $script
        );
        // Should not contain unescaped dangerous characters in URL
        $this->assertStringNotContainsString('><script>alert', $script);
    }

    /**
     * Test that measurement ID with special characters is properly handled.
     */
    public function test_measurement_id_with_special_characters_is_properly_handled(): void
    {
        $specialId = 'G-TEST-123_ABC-456@test';
        $ga4 = new Ga4Service(['measurement_id' => $specialId]);

        $script = $ga4->render();

        $this->assertStringContainsString($specialId, $script);
        $this->assertStringContainsString('googletagmanager.com/gtag/js', $script);
    }

    // ============================================
    // Class Structure Tests
    // ============================================

    /**
     * Test that Ga4Service class exists.
     */
    public function test_ga4_service_class_exists(): void
    {
        $this->assertTrue(class_exists(Ga4Service::class));
    }

    /**
     * Test that Ga4Service can be instantiated.
     */
    public function test_ga4_service_can_be_instantiated(): void
    {
        $ga4 = new Ga4Service(['measurement_id' => 'G-TEST']);
        $this->assertInstanceOf(Ga4Service::class, $ga4);
    }
}
