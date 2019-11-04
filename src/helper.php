<?php

use isszz\captcha\facade\Captcha;

if (!function_exists('svg_captcha')) {
    /**
     * @param string $config
     */
    function svg_captcha($config = [])
    {
        return (string) Captcha::create($config);
    }
}

if (!function_exists('svg_captcha_check')) {
    /**
     * @param string $value
     * @return bool
     */
    function svg_captcha_check($value)
    {
        return Captcha::check($value);
    }
}
