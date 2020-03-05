<?php

namespace Melihovv\LaravelLogViewer;

use Illuminate\Support\Facades\Config;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    const CONFIG_PATH = __DIR__ . '/../config/log-viewer.php';

    const VIEWS_PATH = __DIR__ . '/../views';

    public function boot()
    {
        $this->loadViewsFrom(self::VIEWS_PATH, 'log-viewer');

        $this->publishes([
            self::CONFIG_PATH => config_path('log-viewer.php'),
        ], 'config');

        $this->publishes([
            self::VIEWS_PATH =>
                resource_path('views/vendor/log-viewer'),
        ], 'views');
    }

    public function register()
    {
        $this->mergeConfigFrom(self::CONFIG_PATH, 'log-viewer');

        $this->app->bind('log-viewer', function () {
            $baseDir = Config::get('log-viewer.base_dir');

            return new LogViewer(
                $baseDir ?: storage_path('logs'),
                Config::get('log-viewer.max_file_size')
            );
        });
    }
}
