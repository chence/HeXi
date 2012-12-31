<?php

/**
 * 视图类
 */
class View {

    /**
     * 目录地址
     * @var string
     */
    public $dir;

    /**
     * 视图数据
     * @var array
     */
    protected $data;

    /**
     * 引入视图
     * @var array
     */
    protected $part;

    /**
     * 视图类的名称
     * @var string
     */
    private $named;

    /**
     * 构造方法
     * @param string $named 表示不同的视图类
     */
    public function __construct($named) {
        $this->named = $named;
        $this->dir   = HeXi::$config['app']['view']['dir'];
        $this->data  = array();
        $this->part  = array();
    }

    /**
     * 添加视图数据
     * @param string $key
     * @param mixed  $value
     * @return View
     */
    public function set($key, $value) {
        eval('$this->data["' . str_replace('.', '"]["', $key) . '"] = $value;');
        return $this;
    }

    /**
     * 添加引入视图
     * @param string $part
     * @param string $tpl
     * @return View
     */
    public function with($part, $tpl) {
        $this->part[$part] = $tpl;
        return $this;
    }

    /**
     * 渲染视图文件
     * @param string $tpl
     * @return string
     */
    public function fetch($tpl) {
        $file = $this->dir . $tpl;
        if (!is_file($file)) {
            Error::stop('Template file "' . $tpl . '" is not invalid !', 404);
        }
        $string = file_get_contents($file);
        foreach ($this->part as $n => $t) {
            $f           = $this->dir . $t;
            $replaceText = is_file($f) ? file_get_contents($f) : '<!-- not found ' . $n . ' -->';
            $string      = str_replace('<!--include:' . $n . '-->', $replaceText, $string);
        }
        ob_start();
        extract($this->data);
        eval('?>' . $string);
        return ob_get_clean();
    }
}
