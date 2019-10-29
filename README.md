

# svg-captcha
> 在php中生成svg格式的验证码  
> thinkphp6 svg-captcha  
> 还没有优化字形这块, 不知道把字形数据缓存下来能不能解决大文件字体处理慢的问题

## 安装

```shell
composer require isszz/svg-captcha -vvv
```

> 接下来将字体放入tp根目录下的config/font目录

## 配置

```php
<?php

// SVG 验证码配置

return [
    'width' => 150, // 宽度
    'height' => 50, // 高度
    'noise' => 5, // 干扰线条的数量
    'inverse' => false, // 反转颜色
    'color' => true, // 文字是否随机色
    'background' => '', // 验证码背景色
    'size' => 4, // 验证码字数
    'ignoreChars' => '', // 验证码字符中排除
    'fontSize' => 52, // 字体大小
    'charPreset' => 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789', // 预设随机字符
    'math' => '', // 计算类型, 如果设置不是+或-则随机两种
    'mathMin' => 1, // 用于计算的最小值
    'mathMax' => 9, // 用于计算的最大值
    'fontName' => 'Comismsh.ttf', // 用于验证码的字体, 建议字体文件不超过3MB
    'salt' => '^%$YU$%%^U#$5', // 用于加密验证码的盐
];
```

## 使用方法

控制器内使用🌰

```php
<?php
declare (strict_types = 1);

namespace app\index\controller;

use think\Response;
use think\Request;
use think\exception\HttpResponseException;

class Captcha
{
    /**
     * 获取验证码, 用于api
    */
    public function index(Request $request)
    {
        $config = $this->BuildParam($request->param());
        
        return json([
            'code' => 0,
            'data' => svg_captcha($config),
            'msg' => 'success',
        ]);
    }

    /**
     * 直接显示svg验证码
    */
    public function svg(Request $request)
    {   
        $config = $this->BuildParam($request->param());

        $response = Response::create(svg_captcha($config))->contentType('image/svg+xml');

        throw new HttpResponseException($response);
    }

    /**
     * 验证输入验证码是否正确
    */
    public function check($code)
    {
        if(svg_captcha_check($code) === true) {
            return json([
                'code' => 0,
                'data' => null,
                'msg' => 'success',
            ]);
        }
        return json([
            'code' => 1,
            'data' => null,
            'msg' => 'error',
        ]);
    }

    /**
     * 根据传入参数组装配置
     * 
     * /captcha/svg//w/200/h/60/s/72/l/5
    */
    public function BuildParam($params = [])
    {
        $config = [];

        if(empty($params)) {
            return [];
        }

        // 模式，1=加法 2=减法， 或者随机两种
        if(!empty($params['m'])) {
            if($params['m'] == 1) {
                $config['math'] = '+';
            } elseif($params['m'] == 2) {
                $config['math'] =  '-';
            } else {
                $config['math'] = 'rand';
            }
        }

        if(!empty($params['w'])) {
            $config['width'] = $params['w'];
        }

        if(!empty($params['h'])) {
            $config['height'] = $params['h'];
        }

        // 文字大小
        if(!empty($params['s'])) {
            $config['fontSize'] = $params['s'];
        }

        // 显示文字数量, 非算数模式有效
        if(!empty($params['l'])) {
            $config['size'] = $params['l'];
        }

        // 干扰线条数量
        if(!empty($params['n'])) {
            $config['noise'] = $params['n'];
        }

        // 背景色, #fefefe
        if(!empty($params['b'])) {
            $config['background'] = $params['b'];
        }

        return $config;
    }
}

```

## 注册进tp验证工具

```php
Validate::maker(function ($validate) {
    $validate->extend('svgcaptcha', function ($value) {
        return svg_captcha_check($value);
    }, ':attribute错误!');
});
```

## 本组件基于如下开源库

- php字体库: [PhenX/php-font-lib](https://github.com/PhenX/php-font-lib)
- svg-captcha nodejs版: [lichaozhy/svg-captcha](https://github.com/lichaozhy/svg-captcha)
