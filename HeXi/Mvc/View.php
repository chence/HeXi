<?php
/**
 *
 * HeXi 快速开发框架
 *
 *
 * @copyright Copyright (c) 2012 <hexiaz.com>
 * @author    : FuXiaoHei <fuxiaohei@hexiaz.com>
 * @create: 12-11-23 - 下午7:45
 * @link      : http://hexiaz.com
 *
 *
 */
/**
 *
 * 视图模板的操作类
 * 带有模板引擎
 * @package Mvc
 * @author  FuXiaoHei <fuxiaohei@hexiaz.com>
 *
 */

class View {

    /**
     * 视图对象的实例
     * @var bool|array
     */
    private static $view = false;

    /**
     * 生成视图对象
     * @param string $dir
     * @return View
     */
    public static function create($dir = 'default') {
        if (!self::$view[$dir]) {
            self::$view[$dir] = new View($dir);
        }
        return self::$view[$dir];
    }

    /**
     * 视图目录
     * @var string
     */
    private $dir;

    /**
     * 视图模板的文件名
     * @var string
     */
    private $template;

    /**
     * 视图需要的数据
     * @var array
     */
    private $data;

    /**
     * 视图文件的后缀名
     * @var string
     */
    private $suffix;

    /**
     * 视图编译的文件夹和开关
     * @var string|bool
     */
    private $compile;

    /**
     * 视图编译文件的过期时间
     * @var int
     */
    private $expire;

    /**
     * 私有的初始化方法
     * @param string $dir
     */
    private function __construct($dir) {
        $this->dir = Config::get('app.view.cmd') . '.' . $dir;
        $this->suffix = Config::get('app.view.suffix');
        $this->compile = Config::get('app.view.compile');
        $this->expire = Config::get('app.view.expire');
        $this->data = array();
    }

    /**
     * 设置模板文件
     * 获取模板文件
     * @param bool|string $name
     * @return string|View
     */
    public function template($name = false) {
        if (!$name) {
            return $this->template;
        }
        $this->template = $name;
        return $this;
    }

    /**
     * 添加数据
     * @param string $key
     * @param mixed  $value
     * @return View
     */
    public function with($key, $value = null) {
        if (is_array($key)) {
            $this->data = array_merge($this->data, $key);
            return $this;
        }
        $string = '$this->data["' . str_replace('.', '"]["', $key) . '"] = $value;';
        eval($string);
        return $this;
    }

    /**
     * 删除数据
     * @param string $key
     * @return View
     */
    public function without($key) {
        $string = 'unset($this->data["' . str_replace('.', '"]["', $key) . '"]);';
        eval($string);
        return $this;
    }

    /**
     * 获取模板文件绝对地址
     * @return string
     */
    private function getTemplateFile() {
        return Register::cmd($this->dir . '.' . $this->template) . '.' . $this->suffix;
    }

    /**
     * 获取模板文件对应的编译文件的绝对地址
     * @param string $template
     * @return string
     */
    private function getCompileFile($template) {
        $template = $this->dir . '.' . $template;
        $compile = $this->compile . '.' . md5($template);
        return Register::cmd($compile) . '.php';
    }

    /**
     * 判断模板文件的编译是否过期了
     * @param string $template
     * @return bool
     */
    public function isExpired($template) {
        if (!$this->compile) {
            return true;
        }
        $compile = $this->getCompileFile($template);
        if (!is_file($compile)) {
            return true;
        }
        if (NOW - filemtime($compile) < $this->expire) {
            return false;
        }
        return true;
    }

    /**
     * 渲染模板和数据
     * @return string
     */
    public function fetch() {
        $realFile = $this->getTemplateFile();
        if (!is_file($realFile)) {
            Error::stop('无法加载模板文件 "' . $realFile . '"');
        }
        #如果没有编译，或者编译过期
        if ($this->compile) {
            $compileFile = $this->getCompileFile($this->template);
            if ($this->isExpired($this->template)) {
                $string = self::compileString(file_get_contents($realFile), $this->dir);
                file_put_contents($compileFile, $string);
            }
            $realFile = $compileFile;
        }
        Event::trigger('appViewFetch');
        return self::render(file_get_contents($realFile), $this->data);
    }

    /**
     * 编译模板
     * @param string $string 字符串
     * @param string $dir    子模板的目录
     * @return string
     */
    private static function compileString($string, $dir) {
        $pattern = '/<!--include:(.*)-->/';
        #没有匹配，返回字符串
        if (preg_match_all($pattern, $string, $matches)) {
            $matches[0] = array_unique($matches[0]);
            $matches[1] = array_unique($matches[1]);
            $subTpl = array_combine($matches[0], $matches[1]);
            #循环布局元素
            foreach ($subTpl as $str => $tpl) {
                $cmd = $dir . '.' . $tpl;
                $file = Register::cmd($cmd) . '.' . Config::get('app.view.suffix');
                if (!is_file($file)) {
                    $string = str_replace($str, $str . '<!-- no -->', $string);
                    continue;
                }
                $string = str_replace($str, file_get_contents($file), $string);
            }
        }
        $string = self::compile($string);
        return $string;
    }

    /**
     * 编译字符串
     * @param string $string
     * @return string
     */
    public static function compile($string) {
        $string = str_replace('<!--foreach(', '<?php foreach(', $string);
        $string = str_replace('<!--for(', '<?php for(', $string);
        $string = str_replace(')-->', '){ ?>', $string);
        $string = str_replace(array( '<!--endforeach-->', '<!--endfor-->' ), '<?php } ?>', $string);
        $string = str_replace('<!--if(', '<?php if(', $string);
        $string = str_replace('<!--elseif(', '<?php }elseif(', $string);
        $string = str_replace('<!--else-->', '<?php }else{ ?>', $string);
        $string = str_replace('<!--endif-->', '<?php } ?>', $string);
        $string = str_replace('{{', '<?php echo ', $string);
        $string = str_replace('<!--{', '<?php ', $string);
        $string = str_replace(array( '}-->', '}}' ), ' ?>', $string);
        return $string;
    }

    /**
     * 渲染模板和数据
     * @param string $string
     * @param array  $data
     * @return string
     */
    public static function render($string, $data) {
        extract($data);
        ob_start();
        eval('?>' . $string);
        $string = ob_get_contents();
        ob_end_clean();
        return $string;
    }

}
