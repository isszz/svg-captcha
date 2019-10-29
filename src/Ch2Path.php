<?php
declare (strict_types = 1);

namespace isszz\captcha;

use isszz\captcha\font\Font;
use isszz\captcha\font\Glyph;

class Ch2Path
{
    public static $font;
    public static $glyph;
    public static $glyphs = [];
    public static $glyphMaps = [];

    /**
     * 生成文字svg path
     * 
     * @param  string  $text
     * @param  array  $opts
     * @return object
     */
    public static function make($text, $opts)
    {
        if(empty(self::$font)) {
            throw new CaptchaException('Please load the font first.');
        }

        if(empty(self::$glyphMaps)) {
            self::$glyphMaps = self::$font->getUnicodeCharMap();
        }

        if(empty(self::$glyphs)) {
            self::$glyphs = self::$font->getData('glyf');
        }

        $head = self::$font->getData('head');
        $hhea = self::$font->getData('hhea');

        $fontSize = $opts['fontSize'];
        $fontScale = bcdiv("{$fontSize}", "{$head['unitsPerEm']}", 18);

        $ascender = $hhea['ascent'];
        $descender = $hhea['descent'];

        self::$glyph = new Glyph($head['unitsPerEm']);

        $glyph = self::charToGlyphPath($text);

        $width = bcmul("{$glyph->width}",  "{$fontScale}", 13);
        $left = bcsub("{$opts['x']}", bcdiv("{$width}", '2', 13), 13);
        $height = bcmul(bcadd("{$ascender}", "{$descender}"), "{$fontScale}", 13);
        $top = bcadd("{$opts['y']}", bcdiv("{$height}", "2", 14), 14);

        $path = self::$glyph->getPath($left, $top, $fontSize);

        foreach($path->commands as $key => $cmd) {
            $path->commands[$key] = self::rndPathCmd($cmd);
        }

        return $path->PathData();
    }

    /**
     * 获取文字的glyph
     * 
     * @param  string  $text
     * @return object
     */
    public static function charToGlyphPath($text)
    {
        $glyphIndex = self::charToGlyphIndex($text);

        $glyph = Arr::get(self::$glyphs, $glyphIndex);

        $glyph->parseData();

        $glyph->width  = (abs($glyph->xMin) + $glyph->xMax);
        $glyph->height  = (abs($glyph->yMin) + $glyph->yMax);

        // build path
        self::$glyph->buildPath($glyph->points);

        return $glyph;
        // return self::$glyph->buildPath($glyph->points);
    }

    /**
     * 获取文字的glyph索引id
     * 
     * @param  string  $text
     * @return mixed
     */
    public static function charToGlyphIndex($text) {
        
        $code = Str::unicode($text);

	    if (self::$glyphMaps) {
            foreach(self::$glyphMaps as $unicode => $glyphIndex) {
                if($unicode == $code) {
                    return $glyphIndex;
                }
            }
	    }
	    return null;
	}

    public static function rndPathCmd($cmd)
    {
        $r = (Random::random() * 0.8) - 0.1;
    
        switch ($cmd['type']) {
            case 'M':
            case 'L':
                $cmd['x'] += $r;
                $cmd['y'] += $r;
                break;
            case 'Q':
            case 'C':
                $cmd['x'] += $r;
                $cmd['y'] += $r;
                $cmd['x1'] += $r;
                $cmd['y1'] += $r;
                break;
            default:
                // Close path cmd
                break;
        }
    
        return $cmd;
    }
}
