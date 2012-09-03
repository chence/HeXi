<?php

/**
 * 图片处理类
 * 支持jpg，png和gif
 * @author FuXiaoHei
 */
class Image {

    /**
     * 左上
     */
    const TOP_LEFT = 1;

    /**
     * 右上
     */
    const TOP_RIGHT = 2;

    /**
     * 中上
     */
    const TOP_CENTER = 3;

    /**
     * 左中
     */
    const MID_LEFT = 4;

    /**
     * 右中
     */
    const MID_RIGHT = 5;

    /**
     * 居中
     */
    const MID_CENTER = 6;

    /**
     * 左下
     */
    const BOTTOM_LEFT = 7;

    /**
     * 右下
     */
    const BOTTOM_RIGHT = 8;

    /**
     * 中下
     */
    const BOTTOM_CENTER = 9;

    /**
     * 需要处理的图片文件地址
     * @var string
     */
    protected $imgFile;

    /**
     * 图片宽度
     * @var int
     */
    protected $width;

    /**
     * 图片高度
     * @var int
     */
    protected $height;

    /**
     * 图片类型
     * 即IMAGETYPE_XXX常量
     * @var int
     */
    protected $type;

    /**
     * 图片的宽/高
     * @var float
     */
    protected $ratio;

    /**
     * 图片MIME类型
     * @var string
     */
    protected $mime;


    /**
     * 初始化
     * @param string $file 图片文件的绝对地址
     */
    public function __construct($file) {
        if (!is_file($file)) {
            HeXi::error('需要处理的图像文件不存在 "' . $file . '"');
        }
        $this->imgFile = $file;
        $this->getInfo();
    }

    /**
     * 获取图片信息
     */
    private function getInfo() {
        $info = getimagesize($this->imgFile);
        if ($info === false) {
            HeXi::error('需要处理的图像文件不支持');
        }
        $this->width = $info[0];
        $this->height = $info[1];
        $this->type = $info[2];
        $this->ratio = $this->width / $this->height;
        $this->mime = $info['mime'];
    }

    /**
     * 获取图片资源对象
     * @param string $file
     * @return bool|resource
     */
    private function getSource($file) {
        switch ($this->type) {
            case 1: //gif
                $img = imagecreatefromgif($file);
                break;
            case 2: //jpg
                $img = imageCreatefromjpeg($file);
                break;
            case 3: //png
                $img = imageCreatefrompng($file);
                break;
            default:
                return false;
        }
        #记住原图的透明通道
        imagesavealpha($img, true);
        return $img;
    }

    /**
     * 按比例改变尺寸
     * @param float $ratio
     * @return resource
     */
    public function resize($ratio) {
        if ($ratio > 100) {
            HeXi::error('不支持放大图片尺寸');
        }
        #计算新尺寸
        $nWidth = (int)($this->width * $ratio);
        $nHeight = (int)($this->height * $ratio);
        #生成新图片资源
        $newSrc = imagecreatetruecolor($nWidth, $nHeight);
        #取消混色模式，只进行像素替换，不进行填充
        imagealphablending($newSrc, false);
        #记住新图的透明通道
        imagesavealpha($newSrc, true);
        #获取原图
        $imgSrc = $this->getSource($this->imgFile);
        #重采样并改变大小，由于是等比例的，复制原图像所有内容到新图像
        imagecopyresampled($newSrc, $imgSrc, 0, 0, 0, 0, $nWidth, $nHeight, $this->width, $this->height);
        #释放原图
        imagedestroy($imgSrc);
        #返回新图
        return $newSrc;
    }


    /**
     * 裁剪图片
     * @param int $width
     * @param int $height
     * @param int $position
     * @return resource
     */
    public function crop($width, $height, $position) {
        if ($width > $this->width || $height > $this->height) {
            HeXi::error('裁剪后尺寸应小于原图和尺寸');
        }
        $newSrc = imagecreatetruecolor($width, $height);
        imagealphablending($newSrc, false);
        imagesavealpha($newSrc, true);
        $imgSrc = $this->getSource($this->imgFile);
        $xy = $this->getXY($width, $height, $position);
        #重采样并复制相同高宽下的内容
        imagecopyresampled($newSrc, $imgSrc, 0, 0, $xy[0], $xy[1], $width, $height, $width, $height);
        #释放原图
        imagedestroy($imgSrc);
        #返回新图
        return $newSrc;
    }

    /**
     * 计算裁剪对应原图的xy起点
     * @param int $width
     * @param int $height
     * @param int $position
     * @return array
     */
    private function getXY($width, $height, $position) {
        if ($position == self::TOP_LEFT) {
            return array(0, 0);
        }
        if ($position == self::TOP_RIGHT) {
            return array($this->width - $width, 0);
        }
        if ($position == self::TOP_CENTER) {
            return array((int)(($this->width - $width) / 2), 0);
        }
        if ($position == self::MID_LEFT) {
            return array(0, (int)(($this->height - $height) / 2));
        }
        if ($position == self::MID_RIGHT) {
            return array($this->width - $width, (int)(($this->height - $height) / 2));
        }
        if ($position == self::MID_CENTER) {
            return array((int)(($this->width - $width) / 2), (int)(($this->height - $height) / 2));
        }
        if ($position == self::BOTTOM_LEFT) {
            return array(0, $this->height - $height);
        }
        if ($position == self::BOTTOM_RIGHT) {
            return array($this->width - $width, $this->height - $height);
        }
        if ($position == self::BOTTOM_CENTER) {
            return array((int)(($this->width - $width) / 2), $this->height - $height);
        }
        return array(0, 0);
    }

    /**
     * 生成缩略图
     * @param string $width
     * @param string $height
     * @param int $position
     * @return resource
     */
    public function thumb($width, $height, $position = Image::MID_CENTER) {
        if ($width > $this->width || $height > $this->height) {
            HeXi::error('缩略图尺寸应小于原图和尺寸');
        }
        #计算比例，确定按照高度还是宽度缩放
        $ratio = $width / $height;
        if ($ratio >= $this->ratio) {
            $nRatio = $width / $this->width;
        } else {
            $nRatio = $height / $this->height;
        }
        #缩放图片
        $imgSrc = $this->resize($nRatio);
        #把缩放后图片的长宽写入属性，用于计算xy值
        $this->width = (int)($this->width * $nRatio);
        $this->height = (int)($this->height * $nRatio);
        #然后裁剪
        $newSrc = imagecreatetruecolor($width, $height);
        imagealphablending($newSrc, false);
        imagesavealpha($newSrc, true);
        $xy = $this->getXY($width, $height, $position);
        imagecopyresampled($newSrc, $imgSrc, 0, 0, $xy[0], $xy[1], $width, $height, $width, $height);
        imagedestroy($imgSrc);
        #回复原有的属性值
        $this->getInfo();
        #返回新图
        return $newSrc;
    }


    /**
     * 水印图片
     * @param string $waterFile
     * @param int $position
     * @return resource
     */
    public function waterImage($waterFile, $position = Image::BOTTOM_RIGHT) {
        if (!is_file($waterFile)) {
            HeXi::error('无法加载水印图片 "' . $waterFile . '"');
        }
        $waterInfo = getimagesize($waterFile);
        if ($waterInfo === false) {
            HeXi::error('需要处理的水印图片文件不支持');
        }
        if ($this->width < $waterInfo[0] || $this->height < $waterInfo[1]) {
            HeXi::error('水印图片应小于原图');
        }
        #生成一个新图像资源
        $newSrc = imagecreatetruecolor($this->width, $this->height);
        #获取原图和水印图像资源
        $imgSrc = $this->getSource($this->imgFile);
        $waterSrc = $this->getSource($waterFile);
        #保持水印图片透明
        imagealphablending($waterSrc, false);
        imagesavealpha($waterSrc, true);
        #获取水印的位置xy
        $xy = $this->getXY($waterInfo[0], $waterInfo[1], $position);
        #复制原图
        imagecopyresampled($newSrc, $imgSrc, 0, 0, 0, 0, $this->width, $this->height, $this->width, $this->height);
        #复制水印图
        imagecopyresampled($newSrc, $waterSrc, $xy[0], $xy[1], 0, 0, $waterInfo[0], $waterInfo[1], $waterInfo[0], $waterInfo[1]);
        return $newSrc;
    }

    /**
     * 保存图片
     * @param resource $imgSrc 图片资源对象
     * @param string $file 保存的绝对地址
     * @return string
     */
    public function saveFile($imgSrc, $file) {
        switch ($this->type) {
            case 1: //gif
                imageGif($imgSrc, $file);
                break;
            case 2: //jpg
                imageJPEG($imgSrc, $file);
                break;
            case 3: //png
                imagepng($imgSrc, $file);
                break;
        }
        imagedestroy($imgSrc);
        return $file;
    }

    /**
     * 显示视图
     * @param resource $imgSrc
     * @param bool $isDelete
     * @return string
     */
    public function show($imgSrc, $isDelete = false) {
        #自动生成文件
        $file = IMAGE_CACHE_PATH . uniqid();
        switch ($this->type) {
            case 1: //gif
                $file .= '.gif';
                imageGif($imgSrc, $file);
                break;
            case 2: //jpg
                $file .= '.jpg';
                imageJPEG($imgSrc, $file);
                break;
            case 3: //png
                $file .= '.png';
                imagepng($imgSrc, $file);
                break;
        }
        imagedestroy($imgSrc);
        #然后把文件交给返回请求类处理
        $res = Response::init();
        $res->image($this->mime, $file, $isDelete);
        return $file;
    }

}
