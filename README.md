Laravel log viewer
==================

Small log viewer for laravel. Looks like this:

![capture d ecran 2014-12-01 a 10 37 18](https://cloud.githubusercontent.com/assets/1575946/5243642/8a00b83a-7946-11e4-8bad-5c705f328bcc.png)

Based on [rap2hpoutre/laravel-log-viewer](https://github.com/rap2hpoutre/laravel-log-viewer).

Enhancements
------------
- navigation to logs in nested folder
- tests

Install
-------
Install via composer
```
composer require rap2hpoutre/laravel-log-viewer
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
