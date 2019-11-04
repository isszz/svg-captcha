<?php

namespace isszz\captcha\facade;

use think\Facade;

class Captcha extends Facade
{
    protected static function getFacadeClass()
    {
        return \isszz\captcha\Captcha::class;
    }
}
