<?php

/**
 * 视图类
 * @author FuXiaoHei
 */
class View {

    /**
     * 单例对象
     * @var View
     */
    private static $obj;

    /**
     * 获取单例
     * @return View
     */
    public static function init() {
        return !self::$obj ? self::$obj = new View() : self::$obj;
    }

    /**
     * 私有化构造方法
     */
    private function __construct() {
        $this->path = VIEW_PATH;
        $this->suffix = VIEW_SUFFIX;
        $this->compile = VIEW_COMPILE_AUTO;
        $this->compilePath = VIEW_COMPILE_PATH;
        $this->compileSuffix = VIEW_COMPILE_SUFFIX;
        $this->compileExpire = VIEW_COMPILE_EXPIRE;
        $this->viewData = array();
    }

    /**
     * 视图地址
     * @var string
     */
    public $path;

    /**
     * 视图后缀名
     * @var string
     */
    public $suffix;

    /**
     * 视图是否编译
     * @var bool
     */
    public $compile;

    /**
     * 视图编译地址
     * @var string
     */
    public $compilePath;

    /**
     * 视图编译后缀名
     * @var string
     */
    public $compileSuffix;

    /**
     * 视图编译过期时间
     * @var int
     */
    public $compileExpire;

    /**
     * 视图数据
     * @var array
     */
    public $viewData;

    /**
     * 渲染文件
     * @param string $file
     * @return string
     */
    public function fetch($file) {
        #寻找视图文件
        $viewFile = $this->path . $file . '.' . $this->suffix;
        if (!is_file($viewFile)) {
            HeXi::error('无法加载视图文件 "' . $viewFile . '"');
        }
        #先决定是否要编译
        if ($this->compile) {
            #编译视图内容，使用视图文件
            $compileFile = $this->compilePath . md5($viewFile) . '.' . $this->compileSuffix;
            if (filemtime($compileFile) + $this->compileExpire < time()) {
                $string = self::compileLayout($viewFile);
                $string = self::compileString($string);
                $string .= '<!-- compiled at ' . date('y.m.d H:i:s') . ' -->';
                file_put_contents($compileFile, $string);
            }
        }else{
            #不编译直接使用视图文件
            $compileFile = $viewFile;
        }
        ob_start();
        #展开数据
        extract($this->viewData);
        include $compileFile;
        #获取内容
        $content = ob_get_contents();
        ob_end_clean();
        return $content;
    }

    /**
     * 编译布局视图
     * @param string $file
     * @param bool $hasChild
     * @return mixed|string
     */
    public static function compileLayout($file, $hasChild = false) {
        #获取匹配布局内容
        $string = file_get_contents($file);
        $pattern = '/<!--layout:(.*)-->/';
        #没有匹配，返回字符串
        if (!preg_match_all($pattern, $string, $matches)) {
            return $string;
        }
        $matches[0] = array_unique($matches[0]);
        $matches[1] = array_unique($matches[1]);
        $subTpl = array_combine($matches[0], $matches[1]);
        #循环布局元素
        foreach ($subTpl as $str => $tpl) {
            #通过视图文件判断嵌入视图的位置
            $tpl = dirname($file) . '/' . $tpl . '.' . VIEW_SUFFIX;
            if (!is_file($tpl)) {
                HeXi::error('嵌入的视图文件 "' . $tpl . '" 无法加载');
            }
            if ($hasChild) {
                #编译嵌入式图的嵌入视图，只处理第二级
                $string = str_replace($str, self::compileLayout($tpl), $string);
            } else {
                $string = str_replace($str, file_get_contents($tpl), $string);
            }
        }
        return $string;
    }

    /**
     * 编译字符串
     * @param string $string
     * @return string
     */
    public static function compileString($string) {
        $string = str_replace('<!--foreach(', '<?php foreach(', $string);
        $string = str_replace('<!--for(', '<?php for(', $string);
        $string = str_replace(')-->', '){ ?>', $string);
        $string = str_replace(array('<!--endforeach-->', '<!--endfor-->'), '<?php } ?>', $string);
        $string = str_replace('<!--if(', '<?php if(', $string);
        $string = str_replace('<!--elseif(', '<?php }elseif(', $string);
        $string = str_replace('<!--else-->', '<?php }else{ ?>', $string);
        $string = str_replace('<!--endif-->', '<?php } ?>', $string);
        $string = str_replace('{{', '<?php echo ', $string);
        $string = str_replace('<!--{', '<?php ', $string);
        $string = str_replace(array('}-->', '}}'), ' ?>', $string);
        return $string;
    }

    /**
     * 渲染字符串
     * @param string $string
     * @param array $viewData
     * @return string
     */
    public static function renderString($string, $viewData = array()) {
        $string = self::compileString($string);
        #生成一个临时文件，写入编译后字符串
        $file = __DIR__ . uniqid() . '.' . VIEW_COMPILE_SUFFIX;
        file_put_contents($file, $string);
        #开启缓冲，解析编译后字符串
        ob_start();
        extract($viewData);
        include_once $file;
        $content = ob_get_contents();
        ob_end_clean();
        #删除文件，返回编译后内容
        unlink($file);
        return $content;
    }
}
