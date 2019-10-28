<?php

namespace isszz\captcha;

class FontLibException extends \Exception
{
    public function __construct($message = null)
    {
        !is_null($message) && $this->message = $message;
    }
}