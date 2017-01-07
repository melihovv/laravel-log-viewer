<?php

namespace Melihovv\LaravelLogViewer;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;

class LaravelLogViewerServiceProvider extends ServiceProvider
{
    const CONFIG_PATH = __DIR__ . '/../config/laravel-log-viewer.php';

    const VIEWS_PATH = __DIR__ . '/../views';

    public function boot()
    {
        $this->loadViewsFrom(self::VIEWS_PATH, 'laravel-log-viewer');

        $this->publishes([
            self::CONFIG_PATH => config_path('laravel-log-viewer.php'),
        ], 'config');

        $this->publishes([
            self::VIEWS_PATH =>
                resource_path('views/vendor/laravel-log-viewer'),
        ], 'views');
    }

    public function register()
    {
        $this->mergeConfigFrom(
            self::CONFIG_PATH,
            'laravel-log-viewer'
        );

        $this->app->bind('log-viewer', function () {
            return new LaravelLogViewer(
                Config::get('laravel-log-viewer.base_dir'),
                Config::get('laravel-log-viewer.max_file_size')
            );
        });
    }
}
