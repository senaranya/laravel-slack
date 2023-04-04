<?php

declare(strict_types=1);

namespace Aranyasen\LaravelSlack;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider as IlluminateServiceProvider;

class SlackServiceProvider extends IlluminateServiceProvider
{
    public function register()
    {
        // $this->mergeConfigFrom(
        //     __DIR__.'/../config/chatwork.php',
        //     'chatwork'
        // );

        $this->app->bind('laravel-slack', function (Application $app) {
            // $token = $app->make('config')->get('chatwork.api_key');
            // $auth = new APIToken($token);
            return new SlackNotification();
        });
    }
}
