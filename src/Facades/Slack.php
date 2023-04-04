<?php

declare(strict_types=1);

namespace Aranyasen\LaravelSlack\Facades;

use Illuminate\Support\Facades\Facade;

class Slack extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'laravel-slack';
    }
}
