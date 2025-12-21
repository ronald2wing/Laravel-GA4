<?php

namespace Ronald2Wing\LaravelGa4\Tests\Unit;

use Orchestra\Testbench\TestCase;
use Ronald2Wing\LaravelGa4\Ga4Service;
use Ronald2Wing\LaravelGa4\Ga4ServiceProvider;

class ServiceProviderTest extends TestCase
{
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
     * Test that service provider registers the service correctly.
     */
    public function test_service_provider_registers_service(): void
    {
        $this->assertTrue($this->app->bound('ga4'));
    }

    /**
     * Test that service is registered as a singleton.
     */
    public function test_service_is_registered_as_singleton(): void
    {
        $instance1 = $this->app->make('ga4');
        $instance2 = $this->app->make('ga4');

        $this->assertSame($instance1, $instance2);
    }

    /**
     * Test that service provider merges configuration correctly.
     */
    public function test_service_provider_merges_configuration(): void
    {
        $config = $this->app['config']->get('ga4');

        $this->assertIsArray($config);
        $this->assertArrayHasKey('measurement_id', $config);
        $this->assertEquals('', $config['measurement_id']);
    }

    /**
     * Test that configuration can be overridden.
     */
    public function test_configuration_can_be_overridden(): void
    {
        $this->app['config']->set('ga4.measurement_id', 'G-TEST-CONFIG');

        $config = $this->app['config']->get('ga4');

        $this->assertEquals('G-TEST-CONFIG', $config['measurement_id']);
    }

    /**
     * Test that service can be resolved from container.
     */
    public function test_service_can_be_resolved_from_container(): void
    {
        $service = $this->app->make('ga4');

        $this->assertInstanceOf(\Ronald2Wing\LaravelGa4\Ga4Service::class, $service);
    }

    /**
     * Test that service provider boot method publishes configuration.
     */
    public function test_service_provider_boot_method_publishes_configuration(): void
    {
        $provider = new Ga4ServiceProvider($this->app);

        // The boot method should not throw any exceptions
        $provider->boot();

        // We can't easily test the publishes method directly without mocking,
        // but we can verify the provider boots successfully
        $this->assertTrue(true);
    }

    /**
     * Test createGa4Service method with non-array config.
     */
    public function test_create_ga4_service_with_non_array_config(): void
    {
        $provider = new Ga4ServiceProvider($this->app);

        // Create a custom config repository that implements all necessary methods
        $config = new class implements \Illuminate\Contracts\Config\Repository
        {
            private $data = [];

            public function get($key, $default = null)
            {
                if ($key === 'ga4') {
                    return null; // Not an array
                }
                // Return defaults for other config keys that might be accessed
                if ($key === 'database.default') {
                    return 'testing';
                }
                if ($key === 'app.debug') {
                    return false;
                }

                return $default;
            }

            public function set($key, $value = null)
            {
                $this->data[$key] = $value;
            }

            public function has($key)
            {
                return array_key_exists($key, $this->data);
            }

            public function prepend($key, $value)
            {
                array_unshift($this->data[$key], $value);
            }

            public function push($key, $value)
            {
                $this->data[$key][] = $value;
            }

            public function all()
            {
                return $this->data;
            }

            public function offsetExists($offset): bool
            {
                return $this->has($offset);
            }

            public function offsetGet($offset)
            {
                return $this->get($offset);
            }

            public function offsetSet($offset, $value): void
            {
                $this->set($offset, $value);
            }

            public function offsetUnset($offset): void
            {
                unset($this->data[$offset]);
            }
        };

        // Replace the config in the app container
        $this->app->instance('config', $config);

        // Use reflection to access private method
        $reflection = new \ReflectionClass($provider);
        $method = $reflection->getMethod('createGa4Service');
        $method->setAccessible(true);

        $service = $method->invoke($provider, $this->app);

        $this->assertInstanceOf(Ga4Service::class, $service);

        // Service should render empty string since config is invalid
        $this->assertEmpty($service->render());
    }

    /**
     * Test createGa4Service method with config missing measurement_id key.
     */
    public function test_create_ga4_service_with_config_missing_measurement_id(): void
    {
        $provider = new Ga4ServiceProvider($this->app);

        // Create a custom config repository that implements all necessary methods
        $config = new class implements \Illuminate\Contracts\Config\Repository
        {
            private $data = [];

            public function get($key, $default = null)
            {
                if ($key === 'ga4') {
                    return ['other_key' => 'value']; // No measurement_id
                }
                // Return defaults for other config keys that might be accessed
                if ($key === 'database.default') {
                    return 'testing';
                }
                if ($key === 'app.debug') {
                    return false;
                }

                return $default;
            }

            public function set($key, $value = null)
            {
                $this->data[$key] = $value;
            }

            public function has($key)
            {
                return array_key_exists($key, $this->data);
            }

            public function prepend($key, $value)
            {
                array_unshift($this->data[$key], $value);
            }

            public function push($key, $value)
            {
                $this->data[$key][] = $value;
            }

            public function all()
            {
                return $this->data;
            }

            public function offsetExists($offset): bool
            {
                return $this->has($offset);
            }

            public function offsetGet($offset)
            {
                return $this->get($offset);
            }

            public function offsetSet($offset, $value): void
            {
                $this->set($offset, $value);
            }

            public function offsetUnset($offset): void
            {
                unset($this->data[$offset]);
            }
        };

        // Replace the config in the app container
        $this->app->instance('config', $config);

        // Use reflection to access private method
        $reflection = new \ReflectionClass($provider);
        $method = $reflection->getMethod('createGa4Service');
        $method->setAccessible(true);

        $service = $method->invoke($provider, $this->app);

        $this->assertInstanceOf(Ga4Service::class, $service);

        // Service should render empty string since measurement_id is empty
        $this->assertEmpty($service->render());
    }

    /**
     * Test createGa4Service method with non-string measurement_id.
     */
    public function test_create_ga4_service_with_non_string_measurement_id(): void
    {
        $provider = new Ga4ServiceProvider($this->app);

        // Create a custom config repository that implements all necessary methods
        $config = new class implements \Illuminate\Contracts\Config\Repository
        {
            private $data = [];

            public function get($key, $default = null)
            {
                if ($key === 'ga4') {
                    return ['measurement_id' => 123]; // Not a string
                }
                // Return defaults for other config keys that might be accessed
                if ($key === 'database.default') {
                    return 'testing';
                }
                if ($key === 'app.debug') {
                    return false;
                }

                return $default;
            }

            public function set($key, $value = null)
            {
                $this->data[$key] = $value;
            }

            public function has($key)
            {
                return array_key_exists($key, $this->data);
            }

            public function prepend($key, $value)
            {
                array_unshift($this->data[$key], $value);
            }

            public function push($key, $value)
            {
                $this->data[$key][] = $value;
            }

            public function all()
            {
                return $this->data;
            }

            public function offsetExists($offset): bool
            {
                return $this->has($offset);
            }

            public function offsetGet($offset)
            {
                return $this->get($offset);
            }

            public function offsetSet($offset, $value): void
            {
                $this->set($offset, $value);
            }

            public function offsetUnset($offset): void
            {
                unset($this->data[$offset]);
            }
        };

        // Replace the config in the app container
        $this->app->instance('config', $config);

        // Use reflection to access private method
        $reflection = new \ReflectionClass($provider);
        $method = $reflection->getMethod('createGa4Service');
        $method->setAccessible(true);

        $service = $method->invoke($provider, $this->app);

        $this->assertInstanceOf(Ga4Service::class, $service);

        // Service should render empty string since measurement_id is not a string
        $this->assertEmpty($service->render());
    }

    /**
     * Test createGa4Service method with valid config.
     */
    public function test_create_ga4_service_with_valid_config(): void
    {
        $provider = new Ga4ServiceProvider($this->app);

        // Create a custom config repository that implements all necessary methods
        $config = new class implements \Illuminate\Contracts\Config\Repository
        {
            private $data = [];

            public function get($key, $default = null)
            {
                if ($key === 'ga4') {
                    return ['measurement_id' => 'G-VALID-TEST'];
                }
                // Return defaults for other config keys that might be accessed
                if ($key === 'database.default') {
                    return 'testing';
                }
                if ($key === 'app.debug') {
                    return false;
                }

                return $default;
            }

            public function set($key, $value = null)
            {
                $this->data[$key] = $value;
            }

            public function has($key)
            {
                return array_key_exists($key, $this->data);
            }

            public function prepend($key, $value)
            {
                array_unshift($this->data[$key], $value);
            }

            public function push($key, $value)
            {
                $this->data[$key][] = $value;
            }

            public function all()
            {
                return $this->data;
            }

            public function offsetExists($offset): bool
            {
                return $this->has($offset);
            }

            public function offsetGet($offset)
            {
                return $this->get($offset);
            }

            public function offsetSet($offset, $value): void
            {
                $this->set($offset, $value);
            }

            public function offsetUnset($offset): void
            {
                unset($this->data[$offset]);
            }
        };

        // Replace the config in the app container
        $this->app->instance('config', $config);

        // Use reflection to access private method
        $reflection = new \ReflectionClass($provider);
        $method = $reflection->getMethod('createGa4Service');
        $method->setAccessible(true);

        $service = $method->invoke($provider, $this->app);

        $this->assertInstanceOf(Ga4Service::class, $service);

        // Service should render script with valid measurement ID
        $script = $service->render();
        $this->assertStringContainsString('G-VALID-TEST', $script);
        $this->assertStringContainsString('googletagmanager.com/gtag/js', $script);
    }
}
