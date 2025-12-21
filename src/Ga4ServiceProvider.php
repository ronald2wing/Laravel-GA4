<?php

namespace Ronald2Wing\LaravelGa4;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;

/**
 * Service provider for Laravel GA4 package.
 *
 * Registers configuration and binds GA4 service as singleton.
 */
class Ga4ServiceProvider extends ServiceProvider
{
    /** @var string Service name for dependency injection. */
    public const SERVICE_NAME = 'ga4';

    /** @var string Configuration file name. */
    public const CONFIG_NAME = 'ga4';

    /** @var string Configuration tag for publishing. */
    public const CONFIG_TAG = 'ga4-config';

    /**
     * Register package services.
     *
     * Merges configuration and binds GA4 service as singleton.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/ga4.php', self::CONFIG_NAME);

        $this->app->singleton(self::SERVICE_NAME, function (Application $app): Ga4Service {
            return $this->createGa4Service($app);
        });
    }

    /**
     * Create a new instance of Ga4Service with validated configuration.
     *
     * @param  Application  $app  Laravel application instance
     * @return Ga4Service Configured GA4 service instance
     */
    private function createGa4Service(Application $app): Ga4Service
    {
        /** @var array<string, mixed>|null $config */
        $config = $app['config']->get(self::CONFIG_NAME);

        if (! is_array($config)) {
            $config = [];
        }

        // Ensure measurement_id exists in config array with proper type
        if (! array_key_exists('measurement_id', $config)) {
            $config['measurement_id'] = '';
        }

        // Ensure measurement_id is a string
        if (! is_string($config['measurement_id'])) {
            $config['measurement_id'] = '';
        }

        return new Ga4Service($config);
    }

    /**
     * Bootstrap package services.
     *
     * Publishes configuration file for customization.
     */
    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../config/ga4.php' => $this->app->configPath(self::CONFIG_NAME.'.php'),
        ], self::CONFIG_TAG);
    }
}
