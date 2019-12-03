<?php

namespace YandexMetrica\Facades;

use Illuminate\Support\Facades\Facade;

class YandexMetrica extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \YandexMetrica\Foundation\YandexMetrica::class;
    }
}