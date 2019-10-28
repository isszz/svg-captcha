<?php
declare (strict_types = 1);

namespace isszz\captcha\font;

use isszz\captcha\CaptchaException;

class Font
{
    /**
     * 加载字体
     * 
     * @param  string  $fontName
     * @return mixed
     */
	public static function load(string $fontName = '')
	{
		if(empty($fontName)) {
			throw new CaptchaException('Font file name cannot be empty.');
		}

		if($file = self::getFontFile($fontName) and !file_exists($file)) {
			throw new CaptchaException('Font not found in: ' . $file);
		}

		$header = file_get_contents($file, false, null, 0, 4);
		
		$obj = null;
		switch ($header) {
			case "\x00\x01\x00\x00":
			case 'true':
			case 'typ1':
				$obj = new \isszz\captcha\font\lib\truetype\File;
				break;
			case 'OTTO':
				$obj = new \isszz\captcha\font\lib\opentype\File;
				break;
			case 'wOFF':
				$obj = new \isszz\captcha\font\lib\woff\File;
				break;
			case 'ttcf':
				$obj = new \isszz\captcha\font\lib\truetype\Collection;
				break;
			// Unknown type or EOT
			default:
				$magicNumber = file_get_contents($file, false, null, 34, 2);
				
				if ($magicNumber === 'LP') {
					$obj = new \isszz\captcha\font\lib\eot\File;
				}
		}
		
		if (!is_null($obj)) {
			$obj->load($file);
			return $obj;
		}
		
		return null;
	}

    /**
     * 字体路径
     * 
     * @param  string  $name
     * @return string
     */
    public static function getFontFile(string $name): string
    {
        return app()->getRootPath() . 'config' . DIRECTORY_SEPARATOR . 'font' . DIRECTORY_SEPARATOR . $name;
    }

    /**
     * 获取字形缓存
     * 
     * @param  string  $name
     * @return string
     */
    public static function getGlyphCache(string $name)
    {
        $file = runtime_path('isszz') . 'captcha' . DIRECTORY_SEPARATOR . 'glyph' . DIRECTORY_SEPARATOR . md5($name) . '_' . $name . '.php';

        if(is_file($file)) {
            return include $file;
		}
		
        return false;
    }

    /**
     * 写入字形缓存
     * 
     * @param  string  $name
     * @return string
     */
    public static function setGlyphCache(string $name)
    {
        $file = runtime_path('isszz') . 'captcha' . DIRECTORY_SEPARATOR . 'glyph' . DIRECTORY_SEPARATOR . md5($name) . '_' . $name . '.php';
    }
}
