<?php
declare (strict_types = 1);

namespace isszz\captcha;

use think\Config;
use think\Session;
use isszz\captcha\font\Font;
use isszz\captcha\support\Str;
use isszz\captcha\support\encrypter\Encrypter;

/**
 * SVG 验证码, 中文验证码体积大于5MB的不建议使用
 *
 * 2019-11-05 04:45:36
 */
class Captcha
{
    public $conf = [
        'width' => 150,
        'height' => 50,
        'noise' => 5, // 干扰线条的数量
        'inverse' => false, // 反转颜色
        'color' => true, // 文字是否随机色
        'background' => '#fefefe', // 验证码背景色
        'size' => 4, // 验证码字数
        'ignoreChars' => '', // 验证码字符中排除2
        'fontSize' => 52, // 字体大小
        'charPreset' => 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789', // 预设随机字符
        'math' => '', // 计算类型, 如果设置不是+或-则随机两种
        'mathMin' => 1, // 用于计算的最小值
        'mathMax' => 9, // 用于计算的最大值
        // Comismsh.ttf, yaya.ttf, yahei.ttf
        'fontName' => 'Comismsh.ttf', // 用于验证码的字体, 建议字体文件不超过3MB
    ];

    private $salt = '^%$YU$%%^U#$5'; // 用于加密验证码的盐

    private $session = null;
    private $config = null;

    protected $random;
    protected $ch2path;

    public $svg;
    public $text;
    public $hash;

    public function __construct(Config $config, Session $session)
    {
        $this->config = $config;
        $this->session = $session;
        $this->encrypter = new Encrypter($this->salt);

        return $this;
    }

    /**
     * 创建文字验证码
     *
     * @param array $config
     * @return object
     */
    public function create(array $config = []): Captcha
    {
        if(!empty($config['math'])) {
            return $this->createMath($config);
        }

        $config = $this->getConfig($config);

        $this->initFont($config['fontName']);

        $text = $this->random->captchaText($config);

        $this->svg = $this->generate($text, $config);

        return $this;
    }

    /**
     * 创建计算类型验证码
     *
     * @param array $config
     * @return object
     */
    public function createMath(array $config = []): Captcha
    {
        $config = $this->getConfig($config);

        $this->initFont($config['fontName']);

        list($text, $equation) = $this->random->mathExpr($config['mathMin'], $config['mathMax'], $config['math']);
        $this->svg = $this->generate($equation, $config);

        return $this;
    }

    /**
     * 生成验证码
     *
     * @param string $text
     * @param array $config
     * @return string
     */
    protected function generate(string $text, array $config = []): string
    {
        $text = $text ?: $this->random->captchaText();

        $this->setHash($text);

        $width = $config['width'];
        $height = $config['height'];

        if ($config['background']) {
            $config['color'] = true;
        }

        $bgRect = empty($config['background']) ? '': '<rect width="100%" height="100%" fill="' . $config['background'] . '"/>';

        $paths = array_merge($this->getLineNoise($width, $height, $config), $this->getText($text, $width, $height, $config));

        shuffle($paths);

        $paths = implode('', $paths);

        $start = '<svg xmlns="http://www.w3.org/2000/svg" width="'. $width .'" height="'. $height .'" viewBox="0,0,'. $width .','. $height .'">';

        return $start . $bgRect . $paths . '</svg>';
    }

    /**
     * 生成并写入hash的session
     *
     * @param string $text
     * @return string
     */
    public function setHash(string $text): string
    {
        $text = mb_strtolower($text, 'UTF-8');
        $hash = $this->encrypter->encrypt($text);
        // $hash = password_hash($text, PASSWORD_BCRYPT, ['cost' => 10]);

        $this->session->set('svgcaptcha', $hash);

        $this->text = $text;
        $this->hash = $hash;
    }

    /**
     * 验证验证码是否正确
     *
     * @param string $code 验证码
     * @return bool 验证码是否正确
     */
    public function check(string $code): bool
    {
        if (!$this->session->has('svgcaptcha')) {
            return false;
        }

        $hash = $this->session->get('svgcaptcha');

        $text = is_null($hash) ? null : $this->encrypter->decrypt($hash);
        $res = $code === $text;

        // $code = mb_strtolower($code, 'UTF-8');
        // $res = password_verify($code, $hash);

        if ($res) {
            $this->session->delete('svgcaptcha');
        }

        return $res;
    }

    /**
     * 生成干扰线条
     *
     * @param int $width
     * @param int $height
     * @param array $config
     * @return array
     */
    public function getLineNoise ($width, $height, array $config  = []): array
    {
        $width = (int) $width;
        $height = (int) $height;

        $min = isset($config['inverse']) ? 7 : 1;
        $max = isset($config['inverse']) ? 15 : 9;
        $i = -1;

        $noiseLines = [];
        while (++$i < $config['noise']) {
            $start = Random::randomInt(1, 21) . ' ' . Random::randomInt(1, $height - 1);
            $end = Random::randomInt($width - 21, $width - 1) . ' ' . Random::randomInt(1, $height - 1);
            $mid1 = Random::randomInt(($width / 2) - 21, ($width / 2) + 21) . ' ' . Random::randomInt(1, $height - 1);
            $mid2 = Random::randomInt(($width / 2) - 21, ($width / 2) + 21) . ' ' . Random::randomInt(1, $height - 1);

            $color = $config['color'] ? $this->random->color() : $this->random->greyColor($min, $max);

            $noiseLines[] = '<path d="M' . $start . ' C' . $mid1 . ',' . $mid2 . ',' . $end . '" stroke="' . $color . '" fill="none"/>';
        }

        return $noiseLines;
    }

    /**
     * 获取文字svg path
     *
     * @param string $text
     * @param int $width
     * @param int $height
     * @param array $config
     * @return array
     */
    public function getText(string $text, $width, $height, array $config): array
    {
        $width = (int) $width;
        $height = (int) $height;

        $len = Str::strlen($text);

        $spacing = ($width - 2) / ($len + 1);
        $min = $max = 0;

        if(!empty($config['inverse'])) {
            $min = 10;
            $max = 14;
        }

        $i = -1;

        // 中文, 不建议使用
        if(preg_match ("/[\x{4e00}-\x{9fa5}]/u", $text)) {
            $text = preg_split('/(?<!^)(?!$)/u', $text);
        } else {
            $text = str_split($text);
        }

        $out = [];
        while (++$i < $len) {
            $config['x'] = $spacing * ($i + 1);
            $config['y'] = $height / 2;

            $charPath = $this->ch2path->get($text[$i], $config);

            $color = empty($config['color']) ? $this->random->greyColor($min, $max) : $this->random->color();
            $out[] = '<path fill="' . $color . '" d="' . $charPath . '"/>';
        }

        return $out;
    }

    /**
     * 获取配置
     *
     * @param array $config
     * @return array
     */
    public function getConfig(array $config = []): array
    {
        return array_merge($this->conf, $this->config->get('svgcaptcha'), $config);
    }

    /**
     * 载入字体初始化相关
     *
     * @param string|null $fontName
     */
    public function initFont($fontName = null)
    {
        $this->random = $this->random ?? new Random;
        $this->ch2path = $this->ch2path ?? new Ch2Path($fontName);
    }

    /**
     * 获取验证码
     */
    public function __toString() {
        return $this->svg ?: '';
    }
}
