<?php

declare(strict_types=1);

namespace Ronald2Wing\LaravelGa4;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

final class Ga4ServiceProvider extends ServiceProvider
{
    private const CONFIG_KEY = 'ga4';

    private const PUBLISH_GROUP = 'ga4-config';

    public function register(): void
    {
        $this->mergeConfigFrom($this->configPath(), self::CONFIG_KEY);

        $this->app->singleton(Ga4Tag::class, function (Application $app) {
            return Ga4Tag::fromConfig((array) $app['config']->get(self::CONFIG_KEY, []));
        });
    }

    public function boot(): void
    {
        Blade::directive('ga4', function (): string {
            return '<?php echo app(\Ronald2Wing\LaravelGa4\Ga4Tag::class)->render(); ?>';
        });

        if ($this->app->runningInConsole()) {
            $this->publishes([
                $this->configPath() => $this->app->configPath(self::CONFIG_KEY.'.php'),
            ], self::PUBLISH_GROUP);
        }
    }

    private function configPath(): string
    {
        return __DIR__.'/../config/ga4.php';
    }
}
