<?php

namespace Melihovv\LaravelLogViewer\Tests;

use Illuminate\Support\Facades\Config;
use Melihovv\LaravelLogViewer\Facades\LaravelLogViewer as LaravelLogViewerFacade;
use Melihovv\LaravelLogViewer\LaravelLogViewerServiceProvider;
use Orchestra\Testbench\TestCase;

class LaravelLogViewerConfigTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [LaravelLogViewerServiceProvider::class];
    }

    protected function getPackageAliases($app)
    {
        return [
            'LogViewer' => LaravelLogViewerFacade::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('laravel-log-viewer.base_dir', null);
    }

    public function testItSetsBaseDirToDefaultLaravelLogsDir()
    {
        $this->assertEquals(realpath(storage_path('logs')), \LogViewer::getBaseDirectory());
    }
}
