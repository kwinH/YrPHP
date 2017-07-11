<?php
/**
 * Created by YrPHP.
 * User: Kwin
 * QQ: 284843370
 * Email: kwinwong@hotmail.com
 * GitHub: https://github.com/kwinH/YrPHP
 */
namespace YrPHP;

class VerifyCode
{

//图片对象、宽度、高度、验证码长度

//随机字符串、y轴坐标值、随机颜色
    private $img;
    private $width = 100;//图片宽度
    private $height = 40;//图片高度
    private $size = 21;//字体大小
    private $font;//字体
    private $randStr;//随机字符
    private $len = 4;//随机字符串长度
    private $type;//默认是大小写数字混合型，1 2 3 分别表示 小写、大写、数字型
    private $backColor = '#eeeeee';     //背景色，默认是浅灰色
    private $pixelNum = 666; //干扰点个数
    private $lineNum = 10; //干扰线条数

    public function __construct($config = array())
    {
        if (!session_id()) {
            session_start();
        }
// 验证码长度、图片宽度、高度是实例化类时必需的数据
        $this->init($config);
    }

    public function init($config = array())
    {
        foreach ($config as $k => $v) {
            $this->$k = $v;
        }
        $this->font = empty($this->font) ? BASE_PATH . 'resource/font/1.ttf' : $this->font;
        return $this;
    }

    /**
     * @param string $code 验证码key,用于session获取，默认verify
     * @param bool $line 是否显示干扰线
     * @param bool $pixel 是否显示干扰点
     */
    public function show($code = 'verify', $line = true, $pixel = true)
    {
        $this->setText();
        $_SESSION[$code] = strtolower($this->randStr);
        if ($pixel) {
            $this->interferingPixel();
        }

        if ($line) {
            $this->interferingLine();
        }

        header('Content-Type: image/jpeg');
        imagejpeg($this->img);
    }


    private function setText()
    {
        $this->getStr();
        $this->setBackColor();
        //获取文字信息
        $angle = 0;


        $x = $y = 0;
        for ($i = 0; $i < $this->len; $i++) {
            $color = $this->getRandColor();

            $str = substr($this->randStr, $i, 1);
            $rect = imagettfbbox($this->size, $angle, $this->font, $str);


            $minX = min(array($rect[0], $rect[2], $rect[4], $rect[6]));
            $maxX = max(array($rect[0], $rect[2], $rect[4], $rect[6]));
            $minY = min(array($rect[1], $rect[3], $rect[5], $rect[7]));
            $maxY = max(array($rect[1], $rect[3], $rect[5], $rect[7]));

            //字体位置
            $fontInfo = array(
                "left" => abs($minX) - 1,
                "top" => abs($minY) - 1,
                "width" => $maxX - $minX,
                "height" => $maxY - $minY,
                "box" => $rect
            );


//            if ($i == 0) {
//                $x += $fontInfo['left'];
//            } else {
//                $x += $fontInfo['width'];
//            }

            $x = ($this->width / $this->len) * $i;

            $y = $this->height - ($fontInfo['height'] / 2);

            //var_export($y);die;
            imagettftext($this->img, $this->size, $angle, $x, $y, $color, $this->font, $str);
        }


        return $this;
    }

    /**
     * 获得随机字
     */
    private function getStr()
    {
        $str1 = 'abcdefghijkmnpqrstuvwxyz';
        $str2 = 'ABCDEFGHIJKLMNPQRSTUVWXYZ';
        $str3 = '123456789';
        switch ($this->type) {
            case 1:
                $str = $str1;
                break;
            case 2:
                $str = $str2;
                break;
            case 3:
            case 4:
                $str = $str3;
                break;
            default:
                $str = $str1 . $str2 . $str3;
                break;
        }
        $this->randStr = '';
        for ($i = 0; $i < $this->len; $i++) {
            $start = mt_rand(1, strlen($str) - 1);
            $this->randStr .= substr($str, $start, 1);
        }
    }

    /**
     * 设置背景颜色
     * @return $this
     */
    private function setBackColor()
    {
        if (is_string($this->backColor) && 0 === strpos($this->backColor, '#')) {
            $color = str_split(substr($this->backColor, 1), 2);
            $color = array_map('hexdec', $color);
            if (empty($color[3]) || $color[3] > 127) {
                $color[3] = 0;
            }
        } elseif (!is_array($this->backColor)) {
            die('错误的颜色值');
        }

        if (!isset($color[0])) {
            $color[0] = 0;
        }

        if (!isset($color[1])) {
            $color[1] = $color[2] = $color[0];
        }

        $this->img = imagecreatetruecolor($this->width, $this->height);
        // 调整默认颜色
        $color = imagecolorallocatealpha($this->img, $color[0], $color[1], $color[2], $color[3]);//为图像分配颜色
        imagefill($this->img, 0, 0, $color);//区域填充
        return $this;
    }

    /**
     * 获得随机色
     * @return int
     */
    private function getRandColor($alpha = false)
    {
        $alpha = $alpha === false ? 0 : (is_int($alpha) ? $alpha : rand(0, 60));
        return imagecolorallocatealpha($this->img, rand(0, 100), rand(0, 150), rand(0, 200), $alpha);
    }

    /**
     * 添加干扰点
     * @return $this
     */
    private function interferingPixel()
    {
        for ($i = 0; $i < $this->pixelNum; $i++) {
            $color = $this->getRandColor(true);
            imagesetpixel($this->img, rand() % 100, rand() % 100, $color);
        }

        return $this;
    }

    /**
     * 添加干扰线
     * @return $this
     */
    private function interferingLine()
    {
        for ($j = 0; $j < $this->lineNum; $j++) {
            $rand_x = rand(2, $this->width);
            $rand_y = rand(2, $this->height);
            $rand_x2 = rand(2, $this->width);
            $rand_y2 = rand(2, $this->height);
            $color = $this->getRandColor(true);
            imageline($this->img, $rand_x, $rand_y, $rand_x2, $rand_y2, $color);
        }

        return $this;
    }
}