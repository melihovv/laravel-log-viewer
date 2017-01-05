<?php

namespace Melihovv\LaravelLogViewer\Facades;

use Illuminate\Support\Facades\Facade;

class LaravelLogViewer extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'log-viewer';
    }
}
