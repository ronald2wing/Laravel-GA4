<?php

declare(strict_types=1);

namespace Ronald2Wing\LaravelGa4\Tests\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Ronald2Wing\LaravelGa4\Ga4Tag;
use stdClass;

class Ga4TagTest extends TestCase
{
    #[Test]
    public function renders_empty_when_id_is_null(): void
    {
        $this->assertEmpty((new Ga4Tag(null))->render());
    }

    #[Test]
    public function renders_empty_when_id_is_blank(): void
    {
        $this->assertEmpty((new Ga4Tag(''))->render());
    }

    #[Test]
    public function renders_script_with_valid_id(): void
    {
        $script = (new Ga4Tag('G-TEST123'))->render();

        $this->assertStringContainsString('G-TEST123', $script);
        $this->assertStringContainsString('googletagmanager.com/gtag/js', $script);
        $this->assertStringContainsString('window.dataLayer', $script);
    }

    #[Test]
    public function trims_whitespace_from_id(): void
    {
        $script = Ga4Tag::fromConfig(['measurement_id' => '  G-TEST123  '])->render();

        $this->assertStringContainsString('G-TEST123', $script);
        $this->assertStringNotContainsString('  G-TEST123  ', $script);
    }

    #[Test]
    public function handles_very_long_ids(): void
    {
        $longId = 'G-'.str_repeat('A', 100);
        $script = (new Ga4Tag($longId))->render();

        $this->assertStringContainsString($longId, $script);
    }

    #[Test]
    public function from_config_with_valid_id(): void
    {
        $script = Ga4Tag::fromConfig(['measurement_id' => 'G-TEST123'])->render();

        $this->assertStringContainsString('G-TEST123', $script);
    }

    #[Test]
    public function from_config_with_empty_array_renders_empty(): void
    {
        $this->assertEmpty(Ga4Tag::fromConfig([])->render());
    }

    public static function invalidConfigProvider(): array
    {
        return [
            'empty array' => [[]],
            'unrelated key' => [['other_key' => 'value']],
            'null id' => [['measurement_id' => null]],
            'bool id' => [['measurement_id' => false]],
            'int id' => [['measurement_id' => 123]],
            'array id' => [['measurement_id' => []]],
            'object id' => [['measurement_id' => new stdClass]],
        ];
    }

    #[Test]
    #[DataProvider('invalidConfigProvider')]
    public function from_config_renders_empty_for_invalid_shapes(array $config): void
    {
        $this->assertEmpty(Ga4Tag::fromConfig($config)->render());
    }

    #[Test]
    public function from_config_reads_parameters(): void
    {
        $script = Ga4Tag::fromConfig([
            'measurement_id' => 'G-CONFIG',
            'parameters' => ['anonymize_ip' => true],
        ])->render();

        $this->assertStringContainsString('"anonymize_ip":true', $script);
    }

    #[Test]
    public function from_config_ignores_non_array_parameters(): void
    {
        $script = Ga4Tag::fromConfig([
            'measurement_id' => 'G-CONFIG',
            'parameters' => 'not-an-array',
        ])->render();

        $this->assertStringContainsString("gtag('config', \"G-CONFIG\");", $script);
    }

    #[Test]
    public function is_enabled_when_id_is_valid(): void
    {
        $this->assertTrue((new Ga4Tag('G-TEST123'))->isEnabled());
    }

    #[Test]
    public function is_disabled_when_id_is_null(): void
    {
        $this->assertFalse((new Ga4Tag(null))->isEnabled());
    }

    #[Test]
    public function is_disabled_when_id_is_invalid(): void
    {
        $this->assertFalse(Ga4Tag::fromConfig(['measurement_id' => 'invalid-id'])->isEnabled());
    }

    #[Test]
    public function cast_to_string_renders(): void
    {
        $this->assertStringContainsString('G-TEST123', (string) new Ga4Tag('G-TEST123'));
    }

    #[Test]
    public function to_html_returns_render_output(): void
    {
        $tag = new Ga4Tag('G-TEST123');

        $this->assertSame($tag->render(), $tag->toHtml());
    }

    #[Test]
    public function renders_gtag_config_with_parameters(): void
    {
        $script = (new Ga4Tag('G-TEST123', parameters: ['send_page_view' => false]))->render();

        $this->assertStringContainsString("gtag('config',", $script);
        $this->assertStringContainsString('"send_page_view":false', $script);
    }

    #[Test]
    public function renders_gtag_config_without_parameters_when_empty(): void
    {
        $script = (new Ga4Tag('G-TEST123', parameters: []))->render();

        $this->assertStringContainsString("gtag('config', \"G-TEST123\");", $script);
    }
}
