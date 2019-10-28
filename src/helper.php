<?php

use isszz\captcha\facade\SvgCaptcha;

if (!function_exists('svg_captcha')) {
    /**
     * @param string $config
     */
    function svg_captcha($config = [])
    {
        return (string) SvgCaptcha::create($config);
    }
}

if (!function_exists('svg_captcha_check')) {
    /**
     * @param string $value
     * @return bool
     */
    function svg_captcha_check($value)
    {
        return SvgCaptcha::check($value);
    }
}
