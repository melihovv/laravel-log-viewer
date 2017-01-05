Laravel log viewer
==================

[![Build Status](https://travis-ci.org/melihovv/laravel-log-viewer.svg?branch=master)](https://travis-ci.org/melihovv/laravel-log-viewer)
[![styleci](https://styleci.io/repos/78041678/shield)](https://styleci.io/repos/78041678)
[![Packagist](https://img.shields.io/packagist/v/melihovv/laravel-log-viewer.svg)]()
[![Packagist](https://img.shields.io/packagist/l/melihovv/laravel-log-viewer.svg)](https://packagist.org/packages/melihovv/laravel-log-viewer)
[![Commitizen friendly](https://img.shields.io/badge/commitizen-friendly-brightgreen.svg)](http://commitizen.github.io/cz-cli/)
[![semantic-release](https://img.shields.io/badge/%20%20%F0%9F%93%A6%F0%9F%9A%80-semantic--release-e10079.svg)](https://github.com/semantic-release/semantic-release)

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

Add facade to `config/app.php` in `aliases` section
```php
'LogViewer' => Melihovv\LaravelLogViewer\Facades\LaravelLogViewer::class,
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
