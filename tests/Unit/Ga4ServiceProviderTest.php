<?php

declare(strict_types=1);

namespace Ronald2Wing\LaravelGa4\Tests\Unit;

use Orchestra\Testbench\TestCase;
use PHPUnit\Framework\Attributes\Test;
use Ronald2Wing\LaravelGa4\Ga4ServiceProvider;
use Ronald2Wing\LaravelGa4\Ga4Tag;
use Throwable;

class Ga4ServiceProviderTest extends TestCase
{
    protected function getPackageProviders($app): array
    {
        return [Ga4ServiceProvider::class];
    }

    #[Test]
    public function binds_ga4_tag_in_container(): void
    {
        $this->assertTrue($this->app->bound(Ga4Tag::class));
        $this->assertInstanceOf(Ga4Tag::class, $this->app->make(Ga4Tag::class));
    }

    #[Test]
    public function ga4_tag_is_a_singleton(): void
    {
        $this->app['config']->set('ga4.measurement_id', 'G-SINGLETON');

        $this->assertSame(
            $this->app->make(Ga4Tag::class),
            $this->app->make(Ga4Tag::class),
        );
    }

    #[Test]
    public function default_config_has_expected_keys(): void
    {
        $config = $this->app['config']->get('ga4');

        $this->assertIsArray($config);
        $this->assertSame('', $config['measurement_id']);
        $this->assertArrayHasKey('parameters', $config);
    }

    #[Test]
    public function config_can_be_overridden_at_runtime(): void
    {
        $this->app['config']->set('ga4.measurement_id', 'G-OVERRIDDEN');

        $this->assertSame(
            'G-OVERRIDDEN',
            $this->app['config']->get('ga4.measurement_id'),
        );
    }

    #[Test]
    public function singleton_freezes_config_at_first_resolution(): void
    {
        $this->app['config']->set('ga4.measurement_id', 'G-INITIAL');
        $tag = $this->app->make(Ga4Tag::class);

        $this->app['config']->set('ga4.measurement_id', 'G-CHANGED');

        $this->assertStringContainsString('G-INITIAL', $tag->render());
        $this->assertStringNotContainsString('G-CHANGED', $tag->render());
    }

    #[Test]
    public function reads_id_from_environment(): void
    {
        $original = getenv('GA4_MEASUREMENT_ID');

        try {
            putenv('GA4_MEASUREMENT_ID=G-FROM-ENV');
            $this->refreshApplication();

            $this->assertSame('G-FROM-ENV', $this->app['config']->get('ga4.measurement_id'));
        } finally {
            $original === false
                ? putenv('GA4_MEASUREMENT_ID')
                : putenv('GA4_MEASUREMENT_ID='.$original);
        }
    }

    #[Test]
    public function falls_back_to_empty_when_env_unset(): void
    {
        putenv('GA4_MEASUREMENT_ID');
        $this->refreshApplication();

        $this->assertSame('', $this->app['config']->get('ga4.measurement_id'));
    }

    #[Test]
    public function blade_directive_renders_script_when_enabled(): void
    {
        $this->app['config']->set('ga4.measurement_id', 'G-DIRECTIVE');

        $output = $this->renderBlade('@ga4');

        $this->assertStringContainsString('G-DIRECTIVE', $output);
        $this->assertStringContainsString('googletagmanager.com/gtag/js', $output);
    }

    #[Test]
    public function blade_directive_renders_empty_when_disabled(): void
    {
        $this->app['config']->set('ga4.measurement_id', '');

        $this->assertSame('', $this->renderBlade('@ga4'));
    }

    #[Test]
    public function resolves_and_renders_via_container(): void
    {
        $this->app['config']->set('ga4.measurement_id', 'G-INTEGRATION');

        $script = $this->app->make(Ga4Tag::class)->render();

        $this->assertStringContainsString('G-INTEGRATION', $script);
        $this->assertStringContainsString('googletagmanager.com/gtag/js', $script);
    }

    #[Test]
    public function renders_empty_when_configured_id_is_blank(): void
    {
        $this->app['config']->set('ga4.measurement_id', '');

        $this->assertEmpty($this->app->make(Ga4Tag::class)->render());
    }

    private function renderBlade(string $template): string
    {
        $compiled = $this->app['blade.compiler']->compileString($template);

        ob_start();
        try {
            eval('?>'.$compiled);
        } catch (Throwable $e) {
            ob_end_clean();
            $this->fail('Blade eval failed: '.$e->getMessage());
        }

        return (string) ob_get_clean();
    }
}
