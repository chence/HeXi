<?php
/**
 * 视图类
 */
class HeXiView {

    /**
     * 单例对象
     * @var HeXiView
     */
    protected static $instance;

    /**
     * 获取单例
     * @static
     * @return HeXiView
     */
    public static function instance() {
        if (!self::$instance instanceof HeXiView) {
            self::$instance = new HeXiView();
        }
        return self::$instance;
    }

    /**
     * 视图文件夹
     * @var string
     */
    protected $path;

    /**
     * 是否编译
     * @var bool
     */
    protected $isCompile;

    /**
     * 编译文件夹
     * @var string
     */
    protected $compileDir;

    /**
     * 编译文件过期时间
     * @var int
     */
    protected $compileExpire;

    /**
     * 编译文件后缀名
     * @var string
     */
    protected $compileExt;

    /**
     * 视图文件后缀名
     * @var string
     */
    protected $viewExt;

    /**
     * 视图数据
     * @var array
     */
    protected $viewData;


    /**
     * 初始化，载入默认的配置
     */
    private function __construct() {
        $this->path = HeXiWebApp::getPath() . HeXiConfig::get('view', 'path');
        $this->isCompile = HeXiConfig::get('view', 'compile');
        $this->compileDir = HeXiWebApp::getPath() . HeXiConfig::get('view', 'compile_path');
        $this->compileExpire = HeXiConfig::get('view', 'compile_expire');
        $this->compileExt = HeXiConfig::get('view', 'compile_ext');
        $this->viewExt = HeXiConfig::get('view', 'ext');
        $this->viewData = array();
    }

    /**
     * 设置视图文件夹
     * @param string $dir
     * @return HeXiView
     * @throws HeXiViewException
     */
    public function setDir($dir) {
        if (!is_dir($dir)) {
            throw new HeXiViewException('Template Directory is invalid !');
        }
        $this->path = $dir;
        return $this;
    }

    /**
     * 获取视图文件夹
     * @return string
     */
    public function getDir() {
        return $this->path;
    }

    /**
     * 开启编译
     * @return HeXiView
     */
    public function openCompile() {
        $this->isCompile = true;
        return $this;
    }

    /**
     * 关闭编译
     * @return HeXiView
     */
    public function closeCompile() {
        $this->isCompile = false;
        return $this;
    }

    /**
     * 设置编译文件夹
     * @param string $dir
     * @return HeXiView
     * @throws HeXiViewException
     */
    public function setCompileDir($dir) {
        if (!is_dir($dir)) {
            throw new HeXiViewException('Template Compiling Directory is invalid !');
        }
        $this->compileDir = $dir;
        return $this;
    }

    /**
     * 获取编译文件夹
     * @return string
     */
    public function getCompileDir() {
        return $this->getCompileDir();
    }

    /**
     * 设置过期时间，可以是负数，说明永远过期
     * @param int $expire
     * @return HeXiView
     */
    public function setCompileExpire($expire) {
        $this->compileExpire = (int)$expire;
        return $this;
    }

    /**
     * 获取过期时间
     * @return int
     */
    public function getCompileExpire() {
        return $this->compileExpire;
    }

    /**
     * 添加视图数据
     * @param string|array $name
     * @param null|mixed $value
     * @return HeXiView
     */
    public function assign($name, $value = null) {
        if ($value === null && is_array($name)) {
            foreach ($name as $k=> $v) {
                $this->assign($k, $v);
            }
            return $this;
        }
        $this->viewData[$name] = $value;
        return $this;
    }

    /**
     * 添加Action数据到视图数据
     * @param array $map
     * @return HeXiView
     */
    public function assign_action($map) {
        foreach ($map as $name=> $assign) {
            $this->assign($name, $GLOBALS['action']['data'][$assign]);
        }
        return $this;
    }

    /**
     * 私有方法
     * 判断编译文件是否有效
     * @param string $compileFile
     * @return bool
     */
    private function isCompiled($compileFile) {
        if (is_file($compileFile)) {
            if (filemtime($compileFile) + $this->compileExpire > time()) {
                return true;
            }
            return false;
        }
        return false;
    }


    /**
     * 记录编译信息
     * @return string
     */
    private function compileLog() {
        return PHP_EOL . "<!-- compiled at " . date('m-d H:i:s') . "-->" . PHP_EOL;
    }


    /**
     * 编译并渲染视图，返回字符串结果
     * @param string $file
     * @return string
     * @throws HeXiViewException
     */
    private function compileView($file) {
        #获取视图文件
        $viewFile = $this->path . $file . '.' . $this->viewExt;
        if (!is_file($viewFile)) {
            throw new HeXiViewException('Template file "' . $file . '.' . $this->viewExt . '" is lost !');
        }
        #最终执行文件先确定为视图文件自己
        $finalFile = $viewFile;
        #如果编译，获取编译后文件
        if ($this->isCompile) {
            $compileFile = $this->compileDir . md5($viewFile) . '.' . $this->compileExt;
            #如果编译文件失效了，重新编译
            if (!$this->isCompiled($compileFile)) {
                $string = HeXiViewCompiler::compileLayout($viewFile);
                $string = HeXiViewCompiler::compile($string);
                $string .= $this->compileLog();
                file_put_contents($compileFile, $string);
                if (HEXI_DEBUG) {
                    HeXiLogger::write('View "' . $file . '.' . $this->viewExt . '" compiled', __METHOD__, __FILE__, __LINE__);
                }
            } else {
                if (HEXI_DEBUG) {
                    HeXiLogger::write('View "' . $file . '.' . $this->viewExt . '" compiling cached', __METHOD__, __FILE__, __LINE__);
                }
            }
            #编译了，把最终执行文件确定为编译后文件
            $finalFile = $compileFile;
        }
        #开启缓冲
        ob_start();
        #展开数据
        extract($this->viewData);
        include_once $finalFile;
        #获取内容
        $content = ob_get_contents();
        #清空缓存
        ob_end_clean();
        #返回内容
        return $content;
    }


    /**
     * 渲染视图，返回字符串
     * @param string $file
     * @return string
     */
    public function fetch($file) {
        return $this->compileView($file);
    }

    /**
     * 渲染视图，并写入到返回请求信息中
     * @param string $file
     * @return bool
     */
    public function display($file) {
        $content = $this->compileView($file);
        HeXiWeb::sendBody($content);
        return true;
    }
}

/**
 * 视图异常类
 */
class HeXiViewException extends HeXiException {

}
