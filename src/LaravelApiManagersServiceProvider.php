<?php

/**
 * @Author: etanasia
 * @Date:   2017-11-28 00:02:15
 * @Last Modified by:   etanasia
 * @Last Modified time: 2017-11-28 09:45:46
 */

namespace Jawaraegov\LaravelApiManagers;

use Illuminate\Support\ServiceProvider;

class LaravelApiManagersServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        include __DIR__.'/LaravelApiManagers.php';
        $this->publishes([
        __DIR__ . '/config' => config_path('/'),
        __DIR__ . '/views' => base_path('resources/views/api_manager'),
        __DIR__ . '/host_keys' => base_path('resources/views/host_keys'),
        __DIR__ . '/controller' => base_path('app/Http/Controllers'),
        __DIR__ . '/middleware' => base_path('app/Http/Middleware'),
        __DIR__ . '/models' => base_path('app'),
        __DIR__ . '/migrations' => base_path('database/migrations'),

        ]);
        $this->commands('Jawaraegov\LaravelApiManagers\Commands\RouteCommands');
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
       $this->mergeConfigFrom(
        __DIR__ . '/config/laravel-api-managers.php', 'laravel-api-managers'
        );
    }
}
