<?php

namespace Storage\SDK;

use Illuminate\Support\ServiceProvider;

class StorageServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton('storage.client', function ($app) {
            $options = $app['config']->get('storage');

            if (!isset($options['api_url'])) {
                throw new \InvalidArgumentException('Not found api_urL config');
            }

            if (!isset($options['access_token'])) {
                throw new \InvalidArgumentException('Not found access_token config');
            }

            return new StorageClient($options['api_url'], $options['access_token']);
        });
    }

    public function boot()
    {
        if ($this->app instanceof LaravelApplication && $this->app->runningInConsole()) {
            $this->publishes([$this->configPath() => config_path('storage.php')], 'storage');
        } elseif ($this->app instanceof LumenApplication) {
            $this->app->configure('storage');
        }
    }

    protected function configPath()
    {
        return __DIR__ . '/../config/storage.php';
    }
}
