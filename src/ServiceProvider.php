<?php

namespace Asd\Paysera;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;

class ServiceProvider extends BaseServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function register()
    {
        include __DIR__ . '/routes.php';

        $configPath = __DIR__ . '/../config/asd.php';
        $this->mergeConfigFrom($configPath, 'asd');
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $configPath = __DIR__ . '/../config/asd.php';
        $this->publishes([$configPath => config_path('asd.php')], 'config');

        $this->app->bind('asd.paysera', function ($app) {
            return new PayseraWrapper();
        });
    }
}
