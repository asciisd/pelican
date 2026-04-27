<?php

namespace Asciisd\Copytrade\Facades;

use Illuminate\Support\Facades\Facade;

class Copytrade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'copytrade';
    }
}