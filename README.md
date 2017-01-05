Laravel log viewer
==================

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

Add Service Provider to `config/app.php` in `providers` section
```php
Melihovv\LaravelLogViewer\LaravelLogViewerServiceProvider::class,
```

Add a route in your web routes file:
```php 
Route::get('logs', '\Melihovv\LaravelLogViewer\LaravelLogViewerController@index');
```

Go to `http://myapp/logs`

Additional
----------

Publish package config if you want customize default values
```
php artisan vendor:publish --tag=config
```

If you want customize package view
```
php artisan vendor:publish --tag=views
```
