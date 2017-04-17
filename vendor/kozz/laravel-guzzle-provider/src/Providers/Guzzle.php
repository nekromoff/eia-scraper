<?php
namespace Kozz\Laravel\Providers;

use Illuminate\Support\ServiceProvider;
use GuzzleHttp\Client;

class Guzzle extends ServiceProvider
{
    public function register()
    {
        $this->app->bind('guzzle', function () {
            return new Client;
        });
    }
}
