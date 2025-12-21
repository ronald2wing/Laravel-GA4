<?php

namespace Ronald2Wing\LaravelGa4\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Ronald2Wing\LaravelGa4\Ga4Service;

class EdgeCaseTest extends TestCase
{
    // ============================================
    // Security Tests
    // ============================================

    /**
     * Data provider for dangerous measurement IDs that could cause XSS attacks.
     *
     * @return array<string, array<string>>
     */
    public static function dangerousMeasurementIdsProvider(): array
    {
        return [
            'script tag' => ['G-TEST"><script>alert("xss")</script>'],
            'img tag with onerror' => ['G-TEST\'"`><img src=x onerror=alert(1)>'],
            'javascript in string' => ['G-TEST";alert("xss");//'],
            'single quote javascript' => ['G-TEST\';alert("xss");//'],
            'backtick javascript' => ['G-TEST`;alert("xss");//'],
        ];
    }

    /**
     * Test that measurement ID is properly escaped to prevent XSS attacks.
     *
     * @dataProvider dangerousMeasurementIdsProvider
     */
    public function test_measurement_id_is_properly_escaped_to_prevent_xss(string $dangerousId): void
    {
        $config = ['measurement_id' => $dangerousId];
        $ga4 = new Ga4Service($config);

        $script = $ga4->render();

        if (! empty($script)) {
            $this->assertStringNotContainsString('><script>alert', $script);
            $this->assertStringContainsString('<script async', $script);
            $this->assertStringContainsString('</script>', $script);
        }
    }

    // ============================================
    // Format Tests
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
            'mixed case' => ['G-AbC123DeF'],
            'with numbers only after G-' => ['G-9876543210'],
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

    // ============================================
    // Unicode and Special Character Tests
    // ============================================

    /**
     * Data provider for measurement IDs with Unicode characters.
     *
     * @return array<string, array<string>>
     */
    public static function unicodeMeasurementIdsProvider(): array
    {
        return [
            'greek letters' => ['G-TEST-αβγδε'],
            'japanese characters' => ['G-TEST-日本語'],
            'emojis' => ['G-TEST-😀🎉'],
            'accented characters with space' => ['G-TEST- café'],
            'cyrillic characters' => ['G-TEST-русский'],
            'arabic characters' => ['G-TEST-العربية'],
        ];
    }

    /**
     * Test that service handles measurement IDs with Unicode characters.
     *
     * @dataProvider unicodeMeasurementIdsProvider
     */
    public function test_handles_measurement_ids_with_unicode_characters(string $unicodeId): void
    {
        $config = ['measurement_id' => $unicodeId];
        $ga4 = new Ga4Service($config);

        $script = $ga4->render();

        if (! empty($script)) {
            $this->assertStringContainsString('<script async', $script);
            $this->assertStringContainsString('</script>', $script);
        }
    }

    /**
     * Data provider for measurement IDs with special URL characters.
     *
     * @return array<string, array<string>>
     */
    public static function specialUrlCharactersProvider(): array
    {
        return [
            'ampersand' => ['G-TEST&param=value'],
            'hash' => ['G-TEST#fragment'],
            'question mark' => ['G-TEST?query=string'],
            'plus' => ['G-TEST+plus'],
            'percent encoded' => ['G-TEST%20encoded'],
            'at symbol' => ['G-TEST@email'],
            'equals sign' => ['G-TEST=value'],
            'semicolon' => ['G-TEST;param'],
            'comma' => ['G-TEST,separated'],
        ];
    }

    /**
     * Test that service handles measurement IDs with special URL characters.
     *
     * @dataProvider specialUrlCharactersProvider
     */
    public function test_handles_measurement_ids_with_special_url_characters(string $specialId): void
    {
        $config = ['measurement_id' => $specialId];
        $ga4 = new Ga4Service($config);

        $script = $ga4->render();

        if (! empty($script)) {
            $this->assertStringContainsString('<script async', $script);
            $this->assertStringContainsString('</script>', $script);
            $this->assertStringContainsString('gtag/js?id=', $script);
        }
    }

    /**
     * Data provider for measurement IDs with newlines and tabs.
     *
     * @return array<string, array<string>>
     */
    public static function whitespaceMeasurementIdsProvider(): array
    {
        return [
            'newline' => ["G-TEST\nNEWLINE"],
            'tab' => ["G-TEST\tTAB"],
            'CRLF' => ["G-TEST\r\nCRLF"],
            'mixed whitespace' => ["G-TEST\n\tMIXED"],
        ];
    }

    /**
     * Test that service handles measurement IDs with newlines and tabs.
     *
     * @dataProvider whitespaceMeasurementIdsProvider
     */
    public function test_handles_measurement_ids_with_newlines_and_tabs(string $id): void
    {
        $config = ['measurement_id' => $id];
        $ga4 = new Ga4Service($config);

        $script = $ga4->render();

        if (! empty($script)) {
            $this->assertStringContainsString('<script async', $script);
            $this->assertStringContainsString('</script>', $script);
        }
    }

    // ============================================
    // Length and Format Tests
    // ============================================

    /**
     * Test that service handles extremely long measurement IDs.
     */
    public function test_handles_extremely_long_measurement_ids(): void
    {
        $longId = 'G-'.str_repeat('A', 497); // G- + 497 As = 500 chars

        $config = ['measurement_id' => $longId];
        $ga4 = new Ga4Service($config);

        $script = $ga4->render();

        $this->assertStringContainsString($longId, $script);
        $this->assertStringContainsString('googletagmanager.com/gtag/js', $script);
    }

    // ============================================
    // Empty and Whitespace Tests
    // ============================================

    /**
     * Test that service handles empty string measurement ID.
     */
    public function test_handles_empty_string_measurement_id(): void
    {
        $config = ['measurement_id' => ''];
        $ga4 = new Ga4Service($config);

        $script = $ga4->render();

        $this->assertEmpty($script);
    }

    /**
     * Data provider for whitespace-only measurement IDs.
     *
     * @return array<string, array<string>>
     */
    public static function whitespaceOnlyIdsProvider(): array
    {
        return [
            'spaces' => ['   '],
            'tab' => ["\t"],
            'newline' => ["\n"],
            'CRLF' => ["\r\n"],
            'mixed whitespace' => [" \t\n\r "],
        ];
    }

    /**
     * Test that service handles whitespace-only measurement ID.
     *
     * @dataProvider whitespaceOnlyIdsProvider
     */
    public function test_handles_whitespace_only_measurement_id(string $whitespaceId): void
    {
        $config = ['measurement_id' => $whitespaceId];
        $ga4 = new Ga4Service($config);

        $script = $ga4->render();

        $this->assertEmpty($script);
    }

    /**
     * Test that service handles measurement IDs with leading/trailing whitespace.
     */
    public function test_handles_measurement_ids_with_leading_trailing_whitespace(): void
    {
        $testCases = [
            'leading space' => ' G-TEST123',
            'trailing space' => 'G-TEST123 ',
            'both spaces' => ' G-TEST123 ',
            'multiple spaces' => '   G-TEST123   ',
            'tabs' => "\tG-TEST123\t",
            'newlines' => "\nG-TEST123\n",
        ];

        foreach ($testCases as $description => $id) {
            $config = ['measurement_id' => $id];
            $ga4 = new Ga4Service($config);

            $script = $ga4->render();

            // After trimming, the script should contain G-TEST123
            $this->assertStringContainsString('G-TEST123', $script, "Failed for: $description");
        }
    }

    // ============================================
    // Invalid Input Tests
    // ============================================

    /**
     * Test that service handles various invalid measurement ID types.
     */
    public function test_handles_various_invalid_measurement_id_types(): void
    {
        $invalidIds = [
            null,
            false,
            true,
            123,
            0,
            1.23,
            [],
            new \stdClass,
        ];

        foreach ($invalidIds as $invalidId) {
            $config = ['measurement_id' => $invalidId];
            $ga4 = new Ga4Service($config);

            $script = $ga4->render();

            $this->assertEmpty($script, 'Should return empty string for invalid ID type: '.gettype($invalidId));
        }
    }

    /**
     * Test that service handles invalid configuration arrays.
     */
    public function test_handles_invalid_configuration_arrays(): void
    {
        $invalidConfigs = [
            [],
            ['other_key' => 'value'],
            ['measurement_id' => null],
            ['measurement_id' => false],
            ['measurement_id' => []],
            ['measurement_id' => new \stdClass],
        ];

        foreach ($invalidConfigs as $invalidConfig) {
            $ga4 = new Ga4Service($invalidConfig);

            $script = $ga4->render();

            $this->assertEmpty($script, 'Should return empty string for invalid config: '.json_encode($invalidConfig));
        }
    }
}
