Laravel log viewer
==================

[![Build Status](https://travis-ci.org/melihovv/laravel-log-viewer.svg?branch=master)](https://travis-ci.org/melihovv/laravel-log-viewer)
[![styleci](https://styleci.io/repos/78041678/shield)](https://styleci.io/repos/78041678)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/7928897b-80ea-4249-9306-2ee3591fd24c/mini.png)](https://insight.sensiolabs.com/projects/7928897b-80ea-4249-9306-2ee3591fd24c)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/melihovv/laravel-log-viewer/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/melihovv/laravel-log-viewer/?branch=master)
[![Coverage Status](https://coveralls.io/repos/github/melihovv/laravel-log-viewer/badge.svg?branch=master)](https://coveralls.io/github/melihovv/laravel-log-viewer?branch=master)

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


Add Service Provider to `config/app.php` in `providers` section (it is optional
step if you use laravel>=5.5 with package auto discovery feature)
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

## Credits

- [Alexander Melihov](https://github.com/melihovv)
- [All contributors](https://github.com/melihovv/laravel-log-viewer/graphs/contributors)
