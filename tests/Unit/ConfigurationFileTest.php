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

    /**
     * Test that configuration file has proper documentation.
     */
    public function test_configuration_file_has_proper_documentation(): void
    {
        $configContent = file_get_contents(__DIR__.'/../../config/ga4.php');

        // Check for documentation comments
        $this->assertStringContainsString('Google Analytics 4 Measurement ID', $configContent);
        $this->assertStringContainsString('GA4_MEASUREMENT_ID', $configContent);
        $this->assertStringContainsString('G-XXXXXXXXXX', $configContent);
        $this->assertStringContainsString('Admin > Data Streams', $configContent);
    }

    /**
     * Test that configuration is valid PHP syntax.
     */
    public function test_configuration_is_valid_php_syntax(): void
    {
        $configPath = __DIR__.'/../../config/ga4.php';

        // Check syntax using PHP's lint option
        exec('php -l '.escapeshellarg($configPath).' 2>&1', $output, $returnCode);

        $this->assertEquals(0, $returnCode, 'Configuration file has PHP syntax errors: '.implode("\n", $output));
    }

    /**
     * Test that configuration file follows PSR-12 coding standards.
     */
    public function test_configuration_file_follows_coding_standards(): void
    {
        $configContent = file_get_contents(__DIR__.'/../../config/ga4.php');

        // Check for proper PHP opening tag
        $this->assertStringStartsWith('<?php', trim($configContent));

        // Check for proper array syntax (short array syntax)
        $this->assertStringContainsString('return [', $configContent);

        // Check for proper indentation (spaces, not tabs)
        $lines = explode("\n", $configContent);

        foreach ($lines as $line) {
            if (! empty(trim($line))) {
                // Check that lines don't start with tabs
                if (str_starts_with($line, "\t")) {
                    throw new \Exception('Line contains tabs: '.substr($line, 0, 50));
                }
            }
        }
    }
}
