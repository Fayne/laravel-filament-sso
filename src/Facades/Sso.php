<?php

namespace Fayne\Sso\Facades;

use Illuminate\Support\Facades\Facade;

class Sso extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'sso';
    }
}
