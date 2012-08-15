<?php
/**
 * 视图类
 * @author fuxiaohei
 * @copyright 12-8-14 fuxiaohei
 *
 */
class view {

    /**
     * 视图文件夹
     * @var string
     */
    protected $path;

    /**
     * 是否编译
     * @var bool
     */
    protected $is_compile;

    /**
     * 编译文件夹
     * @var string
     */
    protected $compile_path;

    /**
     * 编译时间
     * @var int
     */
    protected $compile_expire;

    /**
     * 视图数据
     * @var array
     */
    protected $view_data;

    /**
     * 单例
     * @var view
     */
    private static $view;

    /**
     * 获取单例
     * @static
     * @return view
     */
    public static function init() {
        if (!self::$view instanceof view) {
            self::$view = new view();
        }
        return self::$view;
    }

    /**
     * 私有初始化
     */
    private function __construct() {
        $this->path = App_Path . 'view/';
        $this->is_compile = true;
        $this->compile_path = App_Path . 'compile/';
        $this->compile_expire = (int)$GLOBALS['config']['view']['compile_expire'];
        $this->view_data = array();
    }

    /**
     * 禁止克隆
     * @throws Exception
     */
    public function _clone() {
        throw new Exception('view不能克隆');
    }

    /**
     * 添加视图数据
     * @param string|array $name
     * @param null|mixed $value
     */
    public function assign($name, $value = null) {
        if ($value === null && is_array($name)) {
            foreach ($name as $k=> $v) {
                $this->assign($k, $v);
            }
            return;
        }
        $this->view_data[$name] = $value;
    }

    /**
     * 判断编译文件是否过期
     * @param string $file
     * @return bool
     */
    private function is_compiled($file) {
        if (is_file($file)) {
            if (filemtime($file) + $this->compile_expire > time()) {
                return true;
            }
            return false;
        }
        return false;
    }

    /**
     * 添加编译记录信息
     * @return string
     */
    private function log_compile() {
        return PHP_EOL . "<!-- compiled at " . date('m-d H:i:s') . "-->" . PHP_EOL;
    }

    /**
     * 渲染文件，返回结果字符串
     * @param string $file
     * @return string
     * @throws Exception
     */
    public function render($file) {
        #获取视图文件
        $vfile = $this->path . $file;
        if (!is_file($vfile)) {
            throw new Exception('视图文件 ' . $file . ' 丢失');
        }
        #最终执行文件先确定为视图文件自己
        $final = $vfile;
        #如果编译，获取编译后文件
        if ($this->is_compile) {
            $cfile = $this->compile_path . md5($vfile) . '.php';
            #如果编译文件失效了，重新编译
            if (!$this->is_compiled($cfile)) {
                $string = self::compile_layout($vfile);
                $string = self::compile_string($string);
                $string .= $this->log_compile();
                file_put_contents($cfile, $string);
            }
            #编译了，把最终执行文件确定为编译后文件
            $final = $cfile;
        }
        #开启缓冲
        ob_start();
        #展开数据
        extract($this->view_data);
        include_once $final;
        #获取内容
        $content = ob_get_contents();
        #清空缓存
        ob_end_clean();
        #返回内容
        return $content;
    }

    /**
     * 显示页面
     * @param string $file
     */
    public function display($file) {
        echo $this->render($file);
    }

    /**
     * 编译字符串
     * @static
     * @param string $string
     * @return string
     */
    public static function compile_string($string) {
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
     * 编译布局
     * @static
     * @param string $file
     * @param bool $child 有没有子布局
     * @return mixed|string
     * @throws Exception
     */
    public static function compile_layout($file, $child = false) {
        $string = file_get_contents($file);
        $pattern = '/<!--layout:(.*)-->/';
        if (!preg_match_all($pattern, $string, $matches)) {
            return $string;
        }
        $matches[0] = array_unique($matches[0]);
        $matches[1] = array_unique($matches[1]);
        $subTpl = array_combine($matches[0], $matches[1]);
        foreach ($subTpl as $str => $tpl) {
            #通过视图文件判断嵌入视图的位置
            $tpl = dirname($file) . '/' . $tpl;
            if (!is_file($tpl)) {
                throw new Exception('Layout布局文件 ' . $tpl . ' 没找到！');
            }
            if ($child) {
                #编译嵌入式图的嵌入视图，只处理第二级
                $string = str_replace($str, self::compile_layout($tpl), $string);
            } else {
                $string = str_replace($str, file_get_contents($tpl), $string);
            }
        }
        return $string;
    }
}
