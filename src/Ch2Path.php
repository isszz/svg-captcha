<?php
declare (strict_types = 1);

namespace isszz\captcha;

use isszz\captcha\font\Font;
use isszz\captcha\font\Glyph;
use isszz\captcha\support\Str;
use isszz\captcha\support\Arr;

class Ch2Path
{
    public $font;
    public $glyph;
    public $glyphs = [];
    public $glyphMaps = [];

    public $ascent;
    public $descent;
    public $unitsPerEm;

    public function __construct($fontName)
    {
        if(empty($fontName)) {
            throw new CaptchaException('字体文件名不能为空');
        }

        $this->getGlyph($fontName);
    }

    /**
     * 生成文字svg path
     * 
     * @param  string  $text
     * @param  array  $opts
     * @return object
     */
    public function get($text, $opts)
    {
        if(empty($this->font)) {
            throw new CaptchaException('Please load the font first.');
        }

        $this->glyph = new Glyph($this->unitsPerEm);

        $fontSize = $opts['fontSize'];
        $fontScale = bcdiv("{$fontSize}", "{$this->unitsPerEm}", 18);

        $glyphWidth = $this->charToGlyphPath($text);

        $width = bcmul("{$glyphWidth}",  "{$fontScale}", 13);
        $left = bcsub("{$opts['x']}", bcdiv("{$width}", '2', 13), 13);
        $height = bcmul(bcadd("{$this->ascender}", "{$this->descender}"), "{$fontScale}", 13);
        $top = bcadd("{$opts['y']}", bcdiv("{$height}", "2", 14), 14);

        $path = $this->glyph->getPath($left, $top, $fontSize);

        foreach($path->commands as $key => $cmd) {
            $path->commands[$key] = $this->rndPathCmd($cmd);
        }

        return $path->PathData();
    }

    /**
     * 获取文字的glyph
     * 
     * @param  string  $text
     * @return object
     */
    public function charToGlyphPath($text)
    {
        $glyphIndex = $this->charToGlyphIndex($text);

        $glyph = Arr::get($this->glyphs, $glyphIndex);

        if(empty($glyph)) {
            throw new CaptchaException('Glyph does not exist.');
        }

        $glyph->parseData();

        $glyphWidth  = (abs($glyph->xMin) + $glyph->xMax);
        // $glyph->height  = (abs($glyph->yMin) + $glyph->yMax);

        // build path
        $this->glyph->buildPath($glyph->points);

        return $glyphWidth;
        // return $glyph;
        // return $this->glyph->buildPath($glyph->points);
    }

    /**
     * 获取文字的glyph索引id
     * 
     * @param  string  $text
     * @return mixed
     */
    public function charToGlyphIndex($text) {
        
        $code = Str::unicode($text);

	    if ($this->glyphMaps) {
            foreach($this->glyphMaps as $unicode => $glyphIndex) {
                if($unicode == $code) {
                    return $glyphIndex;
                }
            }
	    }
	    return null;
	}

    /**
     * 获取需要的字形数据
     * 
     * @param  string  $fontName
     */
    public function getGlyph($fontName)
    {
        $this->font = $this->font ?? Font::load($fontName);
        $this->font->parse();

        $this->glyphMaps = $this->font->getUnicodeCharMap();

        $this->glyphs = $this->font->getData('glyf');

        $head = $this->font->getData('head');
        $hhea = $this->font->getData('hhea');

        $this->ascender = $hhea['ascent'];
        $this->descender = $hhea['descent'];
        $this->unitsPerEm = $head['unitsPerEm'];

        // $this->unitsPerEm = Arr::get($this->font->getData('head'), 'unitsPerEm');
        unset($head, $hhea);
    }

    public function rndPathCmd($cmd)
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
