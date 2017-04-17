# Laravel - Guzzle 5 Service Provider
[![Stable Status](https://poser.pugx.org/urakozz/laravel-guzzle/v/stable.png)](https://packagist.org/packages/kozz/laravel-guzzle-provider)

laravel guzzle service provider

## Install With Composer:

### Guzzle ~5.0
```sh
composer require kozz/laravel-guzzle ~5.0
```

Or manualy in composer.json:
```json
"require": {
    "kozz/laravel-guzzle": "~5.0"
}
```

### Guzzle ~6.0
```sh
composer require kozz/laravel-guzzle ~6.0
```

Or manualy in composer.json:
```json
"require": {
    "kozz/laravel-guzzle": "~6.0"
}
```

## Register Service Provider

*/configs/app.php*

```php
    ...
    'providers' => [

        /*
         * Laravel Framework Service Providers...
         */
        ...

        /*
         * Application Service Providers...
         */
        ...
        'Kozz\Laravel\Providers\Guzzle'
    ],
```


## Enable Facade

*/configs/app.php*

```php
    ...
    'aliases' => [
        ...
        'Guzzle' => 'Kozz\Laravel\Facades\Guzzle'
    ],
```

## Usage

### Send request

```php

  $response = \Guzzle::get('https://google.com');
```


### Get instance

```php
    $client = app()->offsetGet('guzzle');
    $client = \Illuminate\Container\Container::getInstance()->offsetGet('guzzle');
    $client = \Kozz\Laravel\Facades\Guzzle::getFacadeRoot();
    $client = \Guzzle::getFacadeRoot();
```
