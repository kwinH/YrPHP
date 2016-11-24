<?php
/**
 * Created by YrPHP.
 * User: Kwin
 * QQ: 284843370
 * Email: kwinwong@hotmail.com
 * GitHub: https://GitHubhub.com/quinnfox/yrphp
 */
namespace YrPHP;


class Image
{
    private $imgPath;
    private $info = array();
    private $img = false;


    public function __construct($imgPath = null)
    {
        if (file_exists($imgPath))
            $this->open($imgPath);
    }


    public function open($imgPath)
    {
        if ($this->img !== false) imagedestroy($this->img);

        $this->imgPath = $imgPath;
        //检测图像文件
        if (!is_file($imgPath)) die('不存在的图像文件');

        //获取图像信息
        $info = getimagesize($imgPath);

        //检测图像合法性
        if (false === $info || (IMAGETYPE_GIF === $info[2] && empty($info['bits']))) {
            die('非法图像文件');
        }

        $pathInfo = pathinfo($imgPath);//array(dirname,basename,extension,filename)
        //设置图像信息
        $this->info = array_merge($pathInfo, array(
            'width' => $info[0],
            'height' => $info[1],
            'type' => image_type_to_extension($info[2], false),
            'mime' => $info['mime'],

        ));

        $this->img = $this->getImg($imgPath, $this->info['type']);
        return $this;
    }

    private function getImg($imgPath, $imgType)
    {
        switch ($imgType) {
            case 'gif':                    //gif
            case 1:
                $img = imagecreatefromgif($imgPath);
                break;
            case 'jpeg':                    //jpg
            case 2:
                $img = imagecreatefromjpeg($imgPath);
                break;
            case 'png':                    //png
            case 3:
                $img = imagecreatefrompng($imgPath);
                break;
            default:
                return false;
                break;
        }
        return $img;
    }

    /**
     * 缩略图
     * @param $config
     * array('width'=>100,'height'=>100,'pre'=>0.5);
     * 如果设置了$Config['per']则按照$Config['per']比例缩放 否则按给定宽高 (X除于原图宽高的最小比例缩放)
     */
    public function thumb($config)
    {

        if (isset($config['per'])) {

            $w = $this->info['width'] * $config['per'];

            $h = $this->info['height'] * $config['per'];

        } else {

            $w = empty($config['width']) ? $this->info['width'] : $config['width'];

            $h = empty($config['width']) ? $this->info['height'] : $config['height'];
        }
        /*
                //计算缩放比例
                $ratio = isset($Config['per']) ? $Config['per']
                    : min($this->info['width'] / $w, $this->info['height'] / $h);
                //设置缩略图的坐标及宽度和高度
                $w = $w * $ratio;
                $h = $h * $ratio;

               */


        $this->cut($this->info['width'], $this->info['height'], 1, $w, $h);

        return $this;

    }

    /**
     * 裁剪图像
     * @param  integer $w 裁剪区域宽度
     * @param  integer $h 裁剪区域高度
     * @param  integer|array $position 裁剪起始位置 有10种状态，0为随机位置；
     *                                 1为顶端居左，2为顶端居中，3为顶端居右；
     *                                 4为中部居左，5为中部居中，6为中部居右；
     *                                 7为底端居左，8为底端居中，9为底端居右；
     *                                 指定位置 array(100,100) | array('x'=>100,'y'=>100)
     * @param  integer $width 图像保存宽度 默认为裁剪区域宽度
     * @param  integer $height 图像保存高度 默认为裁剪区域高度
     */
    public function cut($w, $h, $position = 1, $width = null, $height = null)
    {
        //设置保存尺寸
        empty($width) && $width = $w;
        empty($height) && $height = $h;

        $x = $y = 0;

        switch ($position) {
            case 0: //随机位置
                $x = mt_rand(0, $this->info['width'] - $w);
                $y = mt_rand(0, $this->info['height'] - $h);
                break;
            case 1:
                // 起始坐标即为左上角坐标，无需调整
                break;
            case 2://顶端居中
                $x = ($this->info['width'] - $w) / 2;
                break;
            case 3://顶端居右
                $x = $this->info['width'] - $w;
                break;
            case 4://中部居左
                $y = $this->info['height'] / 2;
                break;
            case 5://中部居中
                $y = $this->info['height'] / 2;
                $x = ($this->info['width'] - $w) / 2;
                break;
            case 6://中部居右
                $y = $this->info['height'] / 2;
                $x = $this->info['width'] - $w;
                break;
            case 7://底部居左
                $y = $this->info['height'] - $h;

                break;
            case 8://底部居中
                $y = $this->info['height'] - $h;
                $x = ($this->info['width'] - $w) / 2;
                break;
            case 9://底部居右
                $y = $this->info['height'] - $h;
                $x = $this->info['width'] - $w;
                break;
            default:
                if (is_array($position)) {
                    list($x, $y) = $position;
                } else {
                    die('不支持的文字位置类型');
                }
                break;
        }

        $dstImg = imagecreatetruecolor($width, $height);
        // 调整默认颜色
        $color = imagecolorallocate($dstImg, 255, 255, 255);
        imagefill($dstImg, 0, 0, $color);

        imagecopyresampled($dstImg, $this->img, 0, 0, $x, $y, $width, $height, $w, $h);//重采样拷贝部分图像并调整大小
        imagedestroy($this->img); //销毁原图
        $this->img = $dstImg; //设置新图像
        $this->info['width'] = $width;
        $this->info['height'] = $height;
        return $this;

    }

    /**
     * 为图片添加文字水印
     * @param    string $water array('str'=>'ok','font'=>'msyh.ttf','color'=>'#ffffff','size'=>20,'angle'=>0,)
     * str水印文字为必填 font字体 color默认黑色 size文字大小默认20，angle文字倾斜度默认0 暂只支持GIF,JPG,PNG格式
     * @param    int $position 水印位置，有10种状态，0为随机位置；
     *                                1为顶端居左，2为顶端居中，3为顶端居右；
     *                                4为中部居左，5为中部居中，6为中部居右；
     *                                7为底端居左，8为底端居中，9为底端居右；
     *                                指定位置 array(100,100) | array('x'=>100,'y'=>100)
     * @return    mixed
     */
    public function text($water = array(), $position = 0)
    {

        $font = isset($water['font']) ? $water['font'] : BASE_PATH . 'resource/font/1.ttf';
        $size = isset($water['size']) ? $water['size'] : 20;
        $color = isset($water['color']) ? $water['color'] : '#000000';
        $angle = isset($water['angle']) ? $water['angle'] : 0;
        /* 设置颜色 */
        if (is_string($color) && 0 === strpos($color, '#')) {
            $color = str_split(substr($color, 1), 2);
            $color = array_map('hexdec', $color);
            if (empty($color[3]) || $color[3] > 127) {
                $color[3] = 0;
            }
        } elseif (!is_array($color)) {
            die('错误的颜色值');
        }
        if (!isset($color[0])) $color[0] = 0;
        if (!isset($color[1])) $color[1] = $color[2] = $color[0];

        $color = imagecolorallocatealpha($this->img, $color[0], $color[1], $color[2], $color[3]);//为图像分配颜色

        //获取文字信息
        $info = imagettfbbox($size, $angle, $font, $water['str']);
        $minx = min($info[0], $info[2], $info[4], $info[6]);
        $maxx = max($info[0], $info[2], $info[4], $info[6]);
        $miny = min($info[1], $info[3], $info[5], $info[7]);
        $maxy = max($info[1], $info[3], $info[5], $info[7]);

        /* 计算文字初始坐标和尺寸 */
        $x = $minx;
        $y = abs($miny);
        $w = $maxx - $minx;
        $h = $maxy - $miny;


        switch ($position) {
            case 0: //随机位置
                $x = mt_rand(0, $this->info['width'] - $minx);
                $y = mt_rand(0, $this->info['height'] - $miny);
                break;
            case 1:
                // 起始坐标即为左上角坐标，无需调整
                break;
            case 2://顶端居中
                $x += ($this->info['width'] - $w) / 2;
                break;
            case 3://顶端居右
                $x += $this->info['width'] - $w;
                break;
            case 4://中部居左
                $y += $this->info['height'] / 2;
                break;
            case 5://中部居中
                $y += $this->info['height'] / 2;
                $x += ($this->info['width'] - $w) / 2;
                break;
            case 6://中部居右
                $y += $this->info['height'] / 2;
                $x += $this->info['width'] - $w;
                break;
            case 7://底部居左
                $y += $this->info['height'] - $h;

                break;
            case 8://底部居中
                $y += $this->info['height'] - $h;
                $x += ($this->info['width'] - $w) / 2;
                break;
            case 9://底部居右
                $y += $this->info['height'] - $h;
                $x += $this->info['width'] - $w;
                break;
            default:
                if (is_array($position)) {
                    list($posx, $posy) = $position;
                    $x += $posx;
                    $y += $posy;
                } else {
                    die('不支持的文字位置类型');
                }
                break;
        }

        imagettftext($this->img, $size, 0, $x, $y, $color, $water['font'], $water['str']);
        //imagestring($this->dstImg,5,200,200,$water,$color);//文字水印

        return $this;
    }



    /**
     * 添加水印图片
     * @param  string $water 水印图片路径
     * @param  integer|array $position 水印位置
     * @param    int $position 水印位置，有10种状态，0为随机位置；
     *                                1为顶端居左，2为顶端居中，3为顶端居右；
     *                                4为中部居左，5为中部居中，6为中部居右；
     *                                7为底端居左，8为底端居中，9为底端居右；
     *                                指定位置 array(100,100) | array('x'=>100,'y'=>100)
     * @param  integer $alpha 水印透明度
     * @param  integer $waterConf array('width'=>100,'height'=>100) 调整水印大小 默认调用原图
     */
    public function watermark($water, $position = 0, $alpha = 100, $waterConf = array())
    {
        //资源检测
        if (empty($this->img)) die('没有可以被添加水印的图像资源');
        if (!is_file($water)) die('水印图像不存在');

        //获取水印图像信息
        $waterInfo = getimagesize($water);
        if (false === $waterInfo || (IMAGETYPE_GIF === $waterInfo[2] && empty($waterInfo['bits']))) {
            die('非法水印文件');
        }
        $x = $y = 0;


        $w = $waterInfo[0];
        $h = $waterInfo[1];

        $water = $this->getImg($water, $waterInfo[2]);

        if (!empty($waterConf)) {
            $waterWidth = empty($waterConf['width']) ? $w : $waterConf['width'];
            $waterHeight = empty($waterConf['height']) ? $h : $waterConf['height'];

            $dstImg = imagecreatetruecolor($waterWidth, $waterHeight);
            // 调整默认颜色
            $color = imagecolorallocatealpha($dstImg, 255, 255, 255, 127);
            imagefill($dstImg, 0, 0, $color);
            imagecopyresampled($dstImg, $water, 0, 0, 0, 0, $waterWidth, $waterHeight, $w, $h);

            imagedestroy($water); //销毁原图
            $water = $dstImg;

            $w = $waterWidth;
            $h = $waterHeight;
        }


        //设定水印图像的混色模式
        imagealphablending($water, true);

        switch ($position) {
            case 0: //随机位置
                $x = mt_rand(0, $this->info['width'] - $w);
                $y = mt_rand(0, $this->info['height'] - $h);
                break;
            case 1:
                // 起始坐标即为左上角坐标，无需调整
                break;
            case 2://顶端居中
                $x = ($this->info['width'] - $w) / 2;
                break;
            case 3://顶端居右
                $x = $this->info['width'] - $w;
                break;
            case 4://中部居左
                $y = $this->info['height'] / 2;
                break;
            case 5://中部居中
                $y = $this->info['height'] / 2;
                $x = ($this->info['width'] - $w) / 2;
                break;
            case 6://中部居右
                $y = $this->info['height'] / 2;
                $x = $this->info['width'] - $w;
                break;
            case 7://底部居左
                $y = $this->info['height'] - $h;

                break;
            case 8://底部居中
                $y = $this->info['height'] - $h;
                $x = ($this->info['width'] - $w) / 2;
                break;
            case 9://底部居右
                $y = $this->info['height'] - $h;
                $x = $this->info['width'] - $w;
                break;
            default:
                if (is_array($position)) {
                    list($x, $y) = $position;
                } else {
                    die('不支持的文字位置类型');
                }
                break;
        }

        //创建用于添加水印的图象资源
        $src = imagecreatetruecolor($this->info['width'], $this->info['height']);

        $color = imagecolorallocate($src, 255, 255, 255);  // 调整默认颜色
        imagefill($src, 0, 0, $color);//区域填充默认颜色

        imagecopy($src, $this->img, 0, 0, $x, $y, $w, $h);//拷贝图像的一部分
        imagecopy($src, $water, 0, 0, 0, 0, $w, $h);
        imagecopymerge($this->img, $src, $x, $y, 0, 0, $w, $h, $alpha);// 拷贝并合并图像的一部分

        //销毁临时图片资源
        imagedestroy($src);

        return $this;
    }

    /**
     * 保存图像
     * @param  string $imgname 图像保存名称
     * @param  string $type 图像类型（gif,jpeg,jpg,png） 为空则按原图类型
     * @param  integer $quality 图像质量
     * @param  boolean $interlace 是否对JPEG类型图像设置隔行扫描
     */
    public function save($imgPath, $type = null, $quality = 80, $interlace = true)
    {
        $type = is_null($type) ? $this->info['type'] : strtolower($type);
        if (empty($imgPath)) {
            $imgPath = $this->imgPath;
        } else {
            if (is_dir($imgPath)) {
                $imgPath = ltrim($imgPath, '/') . '/' . $this->info['basename'];
            } elseif (strrpos($imgPath, '/') === false) {
                $imgPath = $this->info['dirname'] . '/' . $imgPath;
            }
        }

        imageinterlace($this->img, $interlace);
        switch ($type) {
            case 'gif':                    //gif
                $img = imageGIF($this->img, $imgPath);
                break;
            case 'jpeg'://jpg
            case 'jpg':
                $img = imageJPEG($this->img, $imgPath, $quality);
                break;
            case 'png':                    //png
                $img = imagePng($this->img, $imgPath);
                break;
            default:
                return false;
                break;
        }
        imagejpeg($this->img, 'F://test.jpg');

        //imagedestroy($this->img);
    }

    /**
     * 客服端下载
     * @param null $downFileName 文件名 默认为原文件名
     * @param null $type 图像类型（gif,jpeg,jpg,png） 为空则按原图类型
     */
    public function down($downFileName = null, $type = null)
    {
        $this->show($type);
        $downFileName = is_null($downFileName) ? $this->info['basename'] :
            ((strrpos($downFileName, '.') === false) ? $downFileName . $this->info['extension'] : $downFileName);

        header('Content-Disposition: Attachment;filename=' . $downFileName);

    }

    /**
     * 直接在浏览器显示图片
     * @param null $type 图像类型（gif,jpeg,jpg,png） 为空则按原图类型
     * @return bool
     */
    public function show($type = null)
    {
        $type = is_null($type) ? $this->info['type'] : strtolower($type);

        switch ($type) {
            case 'gif':                    //gif
                header('Content-Type: image/gif');
                $img = imageGIF($this->img);
                break;
            case 'jpeg'://jpg
            case 'jpg':
                header('Content-Type: image/jpeg');
                $img = imageJPEG($this->img);
                break;
            case 'png':                    //png
                header('Content-Type: image/png');
                $img = imagePng($this->img);
                break;
            default:
                return false;
                break;
        }

    }

    /**
     * 获得图片的基本信息
     * @return array(dirname,basename,extension,filename,width,height,type,mime)
     */
    public function getInfo()
    {
        return $this->info;
    }

    public function __destruct()
    {
        imagedestroy($this->img);
    }
}