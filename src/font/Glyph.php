<?php

namespace isszz\captcha\font;

use isszz\captcha\CaptchaException;

class Glyph
{
    public $path;

    public function __construct($unitsPerEm)
    {
        $this->path = new Path();
        $this->path->unitsPerEm = $unitsPerEm ?? 1000;
    }

    public function buildPath($points = [])
    {
        if (empty($points)) {
            return $this->path;
        }

        $contours = $this->getContours($points);

        for ($contourIndex = 0; $contourIndex < count($contours); ++$contourIndex) {
            $contour = $contours[$contourIndex];
    
            $prev = null;
            $curr = $contour[count($contour) - 1];
            $next = $contour[0];
    
            if ($curr['onCurve']) {
                $this->path->moveTo($curr['x'], $curr['y']);
            } else {
                if ($next['onCurve']) {
                    $this->path->moveTo($next['x'], $next['y']);
                } else {
                    // If both first and last points are off-curve, start at their middle.
                    $start = [
                        'x' => ($curr['x'] + $next['x']) * 0.5,
                        'y' => ($curr['y'] + $next['y']) * 0.5
                    ];
                    $this->path->moveTo($start['x'], $start['y']);
                }
            }
    
            for ($i = 0; $i < count($contour); ++$i) {
                $prev = $curr;
                $curr = $next;
                $next = $contour[($i + 1) % count($contour)];
    
                if ($curr['onCurve']) {
                    // This is a straight line.
                    $this->path->lineTo($curr['x'], $curr['y']);
                } else {
                    $prev2 = $prev;
                    $next2 = $next;
    
                    if (!$prev['onCurve']) {
                        $prev2 = [
                            'x' => ($curr['x'] + $prev['x']) * 0.5,
                            'y' => ($curr['y'] + $prev['y']) * 0.5
                        ];
                        $this->path->lineTo($prev2['x'], $prev2['y']);
                    }
    
                    if (!$next['onCurve']) {
                        $next2 = [
                            'x' => ($curr['x'] + $next['x']) * 0.5,
                            'y' => ($curr['y'] + $next['y']) * 0.5
                        ];
                    }
    
                    $this->path->lineTo($prev2['x'], $prev2['y']);
                    $this->path->quadraticCurveTo($curr['x'], $curr['y'], $next2['x'], $next2['y']);
                }
            }
        }
    
        $this->path->closePath();

        return $this->path;
    }

    public function getPath($x, $y, $fontSize, $options = [])
    {
        $x = $x ?: 0;
        $y = $y ?: 0;
        
        $fontSize = $fontSize ?: 72;

        if (!$options) $options = [];

        $xScale = $options['xScale'] ?? 0;
        $yScale = $options['yScale'] ?? 0;

        $commands = $this->path->commands;

        $path = new Path();

        $scale = 1 / $this->path->unitsPerEm * $fontSize;

        if (!$xScale) $xScale = $scale;
        if (!$yScale) $yScale = $scale;

        for ($i = 0; $i < count($commands); $i += 1) {
            $cmd = $commands[$i];
            if ($cmd['type'] == 'M') {
                $path->moveTo(
                    $x + ($cmd['x'] * $xScale),
                    $y + (-$cmd['y'] * $yScale)
                );
            } else if ($cmd['type'] == 'L') {
                $path->lineTo(
                    $x + ($cmd['x'] * $xScale),
                    $y + (-$cmd['y'] * $yScale)
                );
            } else if ($cmd['type'] == 'Q') {
                $path->quadraticCurveTo(
                    $x + ($cmd['x1'] * $xScale),
                    $y + (-$cmd['y1'] * $yScale),
                    $x + ($cmd['x'] * $xScale),
                    $y + (-$cmd['y'] * $yScale)
                );
            } else if ($cmd['type'] == 'C') {
                $path->curveTo(
                    $x + ($cmd['x1'] * $xScale),
                    $y + (-$cmd['y1'] * $yScale),
                    $x + ($cmd['x2'] * $xScale),
                    $y + (-$cmd['y2'] * $yScale),
                    $x + ($cmd['x'] * $xScale),
                    $y + (-$cmd['y'] * $yScale)
                );
            } else if ($cmd['type'] == 'Z') {
                $path->closePath();
            }
        }

        return $path;
    }

    public function getContours($points) {
        $contours = [];
        $currentContour = [];
        for ($i = 0; $i < count($points); $i += 1) {
            $pt = $points[$i];
            $currentContour[] = $pt;
            if ($pt['endOfContour']) {
                $contours[] = $currentContour;
                $currentContour = [];
            }
        }

        if(!empty($currentContour) && count($currentContour) !== 0) {
            throw new CaptchaException('There are still points left in the current contour.');
        }
    
        return $contours;
    }
}