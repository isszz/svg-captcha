<?php

namespace isszz\captcha\font;

class Path
{
    public $commands = [];
    public $fill = 'black';
    public $stroke = null;
    public $strokeWidth = 1;
    public $unitsPerEm = 1000;

    /**
     * @param  {number} x
     * @param  {number} y
     */
    public function moveTo ($x, $y)
    {
        $this->commands[] = [
            'type' => 'M',
            'x' => $x,
            'y' => $y
        ];
    }

    /**
     * @param  {number} x
     * @param  {number} y
     */
    public function lineTo($x, $y)
    {
        $this->commands[] = [
            'type' => 'L',
            'x' => $x,
            'y' => $y
        ];
    }

    public function curveTo($x1, $y1, $x2, $y2, $x, $y)
    {
        $this->commands[] = [
            'type' => 'C',
            'x1' => $x1,
            'y1' => $y1,
            'x2' => $x2,
            'y2' => $y2,
            'x' => $x,
            'y' => $y
        ];
    }

    public function bezierCurveTo($x1, $y1, $x2, $y2, $x, $y)
    {
        return $this->curveTo($x1, $y1, $x2, $y2, $x, $y);
    }

    public function quadTo($x1, $y1, $x, $y)
    {
        $this->commands[] = [
            'type' => 'Q',
            'x1' => $x1,
            'y1' => $y1,
            'x' => $x,
            'y' => $y
        ];
    }

    public function quadraticCurveTo($x1, $y1, $x, $y) {
        return $this->quadTo($x1, $y1, $x, $y);
    }

    public function close() {
        $this->commands[] = ['type' => 'Z'];
    }

    public function closePath() {
        return $this->close();
    }

    /**
     * Convert the Path to a string of path data instructions
     * 
     * See http://www.w3.org/TR/SVG/paths.html#PathData
     * @param  {number} [decimalPlaces=2] - The amount of decimal places for floating-point values
     * @return {string}
     */
    public function PathData($decimalPlaces = null)
    {
        $decimalPlaces = $decimalPlaces ?? 2;

        $floatToString = function($v) use($decimalPlaces) {

            if ((int) round($v) === $v) {
                return '' . round($v);
            } else {
                return round($v, $decimalPlaces);
            }
        };

        $packValues = function() use($floatToString) {
            $arguments = func_get_args();
            $s = '';
            for ($i = 0; $i < count($arguments); $i += 1) {
                $v = $arguments[$i];
                if ($v >= 0 && $i > 0) {
                    $s .= ' ';
                }

                $s .= $floatToString($v);
            }
            return $s;
        };

        $d = '';
        for ($i = 0; $i < count($this->commands); $i += 1) {
            $cmd = $this->commands[$i];
            if ($cmd['type'] == 'M') {
                $d .= 'M' . $packValues($cmd['x'], $cmd['y']);
            } else if ($cmd['type'] == 'L') {
                $d .= 'L' . $packValues($cmd['x'], $cmd['y']);
            } else if ($cmd['type'] == 'C') {
                $d .= 'C' . $packValues($cmd['x1'], $cmd['y1'], $cmd['x2'], $cmd['y2'], $cmd['x'], $cmd['y']);
            } else if ($cmd['type'] == 'Q') {
                $d .= 'Q' . $packValues($cmd['x1'], $cmd['y1'], $cmd['x'], $cmd['y']);
            } else if ($cmd['type'] == 'Z') {
                $d .= 'Z';
            }
        }

        return $d;
    }
}
