<?php

// SVG 验证码配置

return [
    'width' => 150,
    'height' => 50,
    'noise' => 5, // 干扰线条的数量
    'inverse' => false, // 反转颜色
    'color' => true, // 文字是否随机色
    'background' => '#fefefe', // 验证码背景色
    'size' => 4, // 验证码字数
    'ignoreChars' => '', // 验证码字符中排除
    'fontSize' => 52, // 字体大小
    'charPreset' => 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789', // 预设随机字符
    'math' => '', // 计算类型, 如果设置不是+或-则随机两种
    'mathMin' => 1, // 用于计算的最小值
    'mathMax' => 9, // 用于计算的最大值
    'salt' => '^%$YU$%%^U#$5', // 用于加密验证码的盐
    'fontName' => 'Comismsh.ttf', // 用于验证码的字体, 建议字体文件不超过3MB
];
