<?php

namespace Melihovv\LaravelLogViewer\Tests;

use Melihovv\LaravelLogViewer\Facades\LogViewer as LogViewerFacade;
use Melihovv\LaravelLogViewer\ServiceProvider;
use Orchestra\Testbench\TestCase;

class LogViewerConfigTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [ServiceProvider::class];
    }

    protected function getPackageAliases($app)
    {
        return [
            'LogViewer' => LogViewerFacade::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('log-viewer.base_dir', null);
    }

    /** @test */
    public function it_sets_base_dir_to_default_laravel_logs_dir()
    {
        $this->assertEquals(realpath(storage_path('logs')), LogViewerFacade::getBaseDirectory());
    }
}
