<?php

declare(strict_types=1);

namespace Aranyasen\LaravelSlack;

use Illuminate\Support\ServiceProvider;

class SlackServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../config/laravel-slack.php' => base_path('config/laravel-slack.php')
        ], 'config');
    }

    public function register(): void
    {
        $this->app->bind('laravel-slack', fn() => new SlackNotification());

        $this->mergeConfigFrom(__DIR__ . '/../config/laravel-slack.php', 'laravel-slack');
    }
}
