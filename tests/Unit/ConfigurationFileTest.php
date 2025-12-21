<?php

namespace Ronald2Wing\LaravelGa4\Tests\Unit;

use PHPUnit\Framework\TestCase;

class ConfigurationFileTest extends TestCase
{
    /**
     * Test that configuration file exists and returns array.
     */
    public function test_configuration_file_exists_and_returns_array(): void
    {
        $configPath = __DIR__.'/../../config/ga4.php';

        $this->assertFileExists($configPath);

        $config = require $configPath;

        $this->assertIsArray($config);
        $this->assertArrayHasKey('measurement_id', $config);
    }

    /**
     * Test that configuration has correct structure.
     */
    public function test_configuration_has_correct_structure(): void
    {
        $config = require __DIR__.'/../../config/ga4.php';

        $this->assertCount(1, $config, 'Configuration should have exactly one key');
        $this->assertArrayHasKey('measurement_id', $config);
        $this->assertIsString($config['measurement_id']);
    }

    /**
     * Test that configuration uses environment variable.
     */
    public function test_configuration_uses_environment_variable(): void
    {
        $config = require __DIR__.'/../../config/ga4.php';

        // The config should use env('GA4_MEASUREMENT_ID', '')
        $this->assertStringContainsString('env(', file_get_contents(__DIR__.'/../../config/ga4.php'));

        // Default value should be empty string
        $this->assertEquals('', $config['measurement_id']);
    }
}
