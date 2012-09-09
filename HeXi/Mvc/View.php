<?php

/**
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
        $this->path = config('view.path');
        $this->suffix = config('view.suffix');
        $this->compile = config('view.compile.auto');
        $this->compilePath = config('view.compile.path');
        $this->compileSuffix = config('view.compile.suffix');
        $this->compileExpire = config('view.compile.expire');
        $this->viewData = array();
        $this->theme = array();
        $this->resetCache();
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
     * 子视图的信息
     * @var array
     */
    public $theme;

    /**
     * 是否Cache
     * @var string
     */
    public $cache;

    /**
     * 缓存对象
     * @var CacheFile
     */
    protected $cacheObject;

    /**
     * 重置Cache对象
     * @return View
     */
    public function resetCache() {
        $this->cache = config('view.cache.use');
        if ($this->cache) {
            import('HeXi.Cache.Cache');
            $this->cacheObject = Cache::get('file');
            $this->cacheObject->path = config('view.cache.path');
            $this->cacheObject->expire = config('view.cache.expire');
            $this->cacheObject->suffix = config('view.cache.suffix');
        }
        return $this;
    }

    /**
     * 编译布局视图
     * @param string $file
     * @param bool $hasChild
     * @return mixed|string
     */
    private function compileLayout($file, $hasChild = false) {
        #获取匹配布局内容
        $string = file_get_contents($file);
        $pattern = '/<!--theme:(.*)-->/';
        #没有匹配，返回字符串
        if (!preg_match_all($pattern, $string, $matches)) {
            return $string;
        }
        $matches[0] = array_unique($matches[0]);
        $matches[1] = array_unique($matches[1]);
        $subTpl = array_combine($matches[0], $matches[1]);
        #循环布局元素
        foreach ($subTpl as $str => $tpl) {
            #判断是不是已经提交的视图
            if (array_key_exists($tpl, $this->theme)) {
                if (is_file($this->theme[$str])) {
                    $tpl = $this->theme[$str];
                } else {
                    #通过视图文件判断嵌入视图的位置
                    $tpl = dirname($file) . DS . $tpl . '.' . $this->suffix;
                    if (!is_file($tpl)) {
                        HeXi::error('嵌入的视图文件 "' . $tpl . '" 无法加载');
                    }
                }
                if ($hasChild) {
                    #编译嵌入式图的嵌入视图，只处理第二级
                    $string = str_replace($str, $this->compileLayout($tpl), $string);
                } else {
                    $string = str_replace($str, file_get_contents($tpl), $string);
                }
            }
        }
        return $string;
    }

    /**
     * 编译字符串
     * @param string $string
     * @return string
     */
    private function compileString($string) {
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
                $string = $this->compileLayout($viewFile);
                $string = $this->compileString($string);
                $string .= "\n" . '<!-- compiled at ' . date('y.m.d H:i:s', NOW) . ' -->';
                file_put_contents($compileFile, $string);
            }
        } else {
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
        if ($this->cache) {
            $content .= "\n" . '<!-- cached at ' . date('y.m.d H:i:s', NOW) . ' -->';
            $this->cacheObject->set(md5($viewFile), $content);
        }
        return $content;
    }

    public function cache($file) {
        #寻找视图文件
        $viewFile = $this->path . $file . '.' . $this->suffix;
        if (!is_file($viewFile)) {
            HeXi::error('无法加载视图文件 "' . $viewFile . '"');
        }
        if ($this->cache) {
            $content = $this->cacheObject->get(md5($viewFile));
            if ($content) {
                return $content;
            }
            return false;
        }
        return false;
    }

    public function display($file) {
        $content = $this->cache($file);
        if (!$content) {
            return $this->fetch($file);
        }
        return $content;
    }

}


