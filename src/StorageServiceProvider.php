<?php

namespace Storage\SDK;

use Illuminate\Foundation\Application as LaravelApplication;
use Illuminate\Support\ServiceProvider;
use Laravel\Lumen\Application as LumenApplication;

class StorageServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton('storage.client', function ($app) {
            $options = $app['config']->get('storage');

            if (!isset($options['api_url'])) {
                throw new \InvalidArgumentException('Not found api_urL config');
            }

            if (!isset($options['oauth']['client_id'])) {
                throw new \InvalidArgumentException('Not found oauth client_id config');
            }

            if (!isset($options['oauth']['client_secret'])) {
                throw new \InvalidArgumentException('Not found oauth client_secret config');
            }

            return new StorageClient($options['api_url']);
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
