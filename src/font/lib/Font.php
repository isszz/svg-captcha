<?php
declare (strict_types = 1);

namespace isszz\captcha\font\lib;

class Font
{
	public static $debug = false;

	public static function d($str) {
		if (!self::$debug) {
			return;
		}
		echo "$str\n";
	}
  
	public static function UTF16ToUTF8($str) {
		return mb_convert_encoding($str, 'utf-8', 'utf-16');
	}
  
	public static function UTF8ToUTF16($str) {
		return mb_convert_encoding($str, 'utf-16', 'utf-8');
	}
}
