Laravel log viewer
==================

![GitHub Workflow Status](https://img.shields.io/github/workflow/status/melihovv/laravel-log-viewer/run-php-tests?label=Tests)
[![styleci](https://styleci.io/repos/78041678/shield)](https://styleci.io/repos/78041678)

[![Packagist](https://img.shields.io/packagist/v/melihovv/laravel-log-viewer.svg)](https://packagist.org/packages/melihovv/laravel-log-viewer)
[![Packagist](https://poser.pugx.org/melihovv/laravel-log-viewer/d/total.svg)](https://packagist.org/packages/melihovv/laravel-log-viewer)
[![Packagist](https://img.shields.io/packagist/l/melihovv/laravel-log-viewer.svg)](https://packagist.org/packages/melihovv/laravel-log-viewer)

Small log viewer for laravel. Looks like this:

![screenshot](https://cloud.githubusercontent.com/assets/8608721/21664637/e34b26e2-d2f8-11e6-8a7e-721f0009adb4.png)

Based on [rap2hpoutre/laravel-log-viewer](https://github.com/rap2hpoutre/laravel-log-viewer).

Enhancements
------------
- navigation to logs in nested folder
- tests

Install
-------
Install via composer
```
composer require melihovv/laravel-log-viewer
```

Add a route in your web routes file:
```php
Route::get('logs', '\Melihovv\LaravelLogViewer\Controller@index');
```

Go to `http://localhost:8000/logs`

Additional
----------

Publish package config if you want to customize default config values or template
```
php artisan vendor:publish --provider="Melihovv\LaravelLogViewerController\ServiceProvider"
```

## Security

If you discover any security related issues, please email amelihovv@ya.ru instead of using the issue tracker.

## Credits

- [Alexander Melihov](https://github.com/melihovv)
- [All contributors](https://github.com/melihovv/laravel-log-viewer/graphs/contributors)
