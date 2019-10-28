<?php
declare (strict_types = 1);

namespace isszz\captcha;

class Random
{
    /**
     * 获取灰色
     *
     * @param int $min
     * @param int $max
     * @return string
     */
    public function greyColor (int $min = 0, int $max = 0): string
    {

        $min = $min ?: 1;
        $max = $max ?: 9;

        $int = bin2hex((string) self::randomInt($min, $max));

        return "#{$int}{$int}{$int}";
    }
    
    /**
     * 获取随机字符
     *
     * @param array $options
     * @return string
     */
    public function captchaText ($options): string
    {
        if (is_numeric($options)) {
            $options['size'] = $options;
        }

        $options = $options ?: [];
    
        $size = $options['size'] ?: 4;
        $ignoreChars = $options['ignoreChars'] ?: '';

        $i = -1;
        $out = '';
        
        $chars = $options['charPreset'];
    
        if ($ignoreChars) {
            $chars = $this->stripCharsFromString($chars, $ignoreChars);
        }
    
        $len = Str::strlen($chars) - 1;
    
        while (++$i < $size) {
            $out .= $chars[self::randomInt(0, $len)];
        }
    
        return $out;
    }

    /**
     * 排除某些字符
     *
     * @param string $string
     * @param string $chars
     * @return string
     */
    public function stripCharsFromString (string $string, string $chars = ''): string
    {
        $array = str_split($string);

        foreach($array as $key => $char) {
            if(stripos($chars, $char) === false) {
                continue;
            }
            $array[$key] = $char;
        }

        return implode('', $array);
    }

    /**
     * 加法
     *
     * @param int $leftNumber
     * @param int $rightNumber
     * @return array
     */
    public function mathExprPlus(int $leftNumber, int $rightNumber): array
    {
        $text = $leftNumber + $rightNumber;
        $equation = $leftNumber . '+' . $rightNumber . '=';
        return [(string) $text, $equation];
    }

    /**
     * 减法
     *
     * @param int $leftNumber
     * @param int $rightNumber
     * @return array
     */
    public function mathExprMinus(int $leftNumber, int $rightNumber): array
    {
        $text = $leftNumber - $rightNumber;
        $equation = $leftNumber . '-' . $rightNumber . '=';
        return [(string) $text, $equation];
    }

    /**
     * 乘法
     *
     * @param int $leftNumber
     * @param int $rightNumber
     * @return array
     */
    public function mathExprMul(int $leftNumber, int $rightNumber): array
    {
        $text = $leftNumber * $rightNumber;
        $equation = $leftNumber . '*' . $rightNumber . '=';
        return [(string) $text, $equation];
    }

    /**
     * 除法
     *
     * @param int $leftNumber
     * @param int $rightNumber
     * @return array
     */
    public function mathExprDiv(int $leftNumber, int $rightNumber): array
    {
        $text = $leftNumber / $rightNumber;
        $equation = $leftNumber . '/' . $rightNumber . '=';
        return [(string) $text, $equation];
    } 
    
    /**
     * Creates a simple math expression using either the + or - operator
     * 
     * @param {number} [min] - The min value of the math expression defaults to 1
     * @param {number} [max] - The max value of the math expression defaults to 9
     * @param {string} [operator] - The operator(s) to use
     * @returns {{equation: string, text: string}}
     */
    public function mathExpr (int $min = 1, int $max = 9, string $operator = ''): array
    {
        $min = $min ?: 1;
        $max = $max ?: 9;

        $operator = $operator ?: '+';

        $left = random_int($min, $max);
        $right = random_int($min, $max);
        switch($operator) {
            case '+':
                return $this->mathExprPlus($left, $right);
            case '-':
                return $this->mathExprMinus($left, $right);
            default:
                return (rand(1, 2) % 2) ? $this->mathExprPlus($left, $right) : $this->mathExprMinus($left, $right);
        }
    }

    /**
     * 获取随机色
     *
     * https://github.com/jquery/jquery-color/blob/master/jquery.color.js#L432
     * The idea here is generate color in hsl first and convert that to rgb color
     * @param int $leftNumber
     * @param int $rightNumber
     * @return array
     */
    public function color ($bgColor = null )
    {
        // Random 24 colors
        // or based on step
        $hue = self::randomInt(0, 24) / 24;

        $saturation = self::randomInt(60, 80) / 100;

        $bgLightness = is_null($bgColor) ? 1.0 : $this->getLightness($bgColor);

        if ($bgLightness >= 0.5) {
            $minLightness = (int) round($bgLightness * 100) - 45;
            $maxLightness = (int) round($bgLightness * 100) - 25;
        } else {
            $minLightness = (int) round($bgLightness * 100) + 25;
            $maxLightness = (int) round($bgLightness * 100) + 45;
        }

        $lightness = self::randomInt($minLightness, $maxLightness) / 100;

        $q = $lightness < 0.5 ? $lightness * ($lightness + $saturation) : $lightness + $saturation - ($lightness * $saturation);

        $p = (2 * $lightness) - $q;

        $r = floor($this->hue2rgb($p, $q, $hue + (1 / 3)) * 255);
        $g = floor($this->hue2rgb($p, $q, $hue) * 255);
        $b = floor($this->hue2rgb($p, $q, $hue - (1 / 3)) * 255);
        /* eslint-disable no-mixed-operators */

        // dd([$r, $g, $b]);
        $color = ($b | $g << 8 | $r << 16) | 1 << 24;

        $color = dechex("{$color}");
        $color = substr($color, 1);

        return '#' . $color;
    }

    public function getLightness($rgbColor)
    {
        $rgbColor = str_split($rgbColor);

        if ($rgbColor[0] !== '#') {
            return 1.0;
        }

        $rgbColor = array_slice($rgbColor, 1);

        if (is_array($rgbColor) && count($rgbColor) === 3) {
            $rgbColor = [
                $rgbColor[0],
                $rgbColor[0],
                $rgbColor[1],
                $rgbColor[1],
                $rgbColor[2],
                $rgbColor[2],
            ];
        }

        $rgbColor = implode('', $rgbColor);

        $hexColor = hexdec("{$rgbColor}");

        $r = $hexColor >> 16;
        $g = $hexColor >> 8 & 255;
        $b = $hexColor & 255;

        $max = max($r, $g, $b);
        $min = min($r, $g, $b);

        return ($max + $min) / (2 * 255);
    }

    public function hue2rgb($p, $q, $h)
    {
        $h = fmod(floatval($h + 1), 1);
        if ($h * 6 < 1) {
            return $p + ($q - $p) * $h * 6;
        }
        if ($h * 2 < 1) {
            return $q;
        }
        if ($h * 3 < 2) {
            return $p + ($q - $p) * ((2 / 3) - $h) * 6;
        }
        return $p;
    }
    
    public static function randomInt(int $min = 0, int $max = 0): int
    {
        return (int) round($min + (self::random() * ($max - $min)));
    }

    public static function random(int $min = 0, int $max = 1): float
    {
        return $min + mt_rand() / mt_getrandmax() * ($max - $min);
    }
}