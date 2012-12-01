<?php
/**
 *
 * HeXi 快速开发框架
 *
 *
 * @copyright Copyright (c) 2012 <hexiaz.com>
 * @author    : FuXiaoHei <fuxiaohei@hexiaz.com>
 * @create: 12-11-28 - 下午7:15
 * @link      : http://hexiaz.com
 *
 *
 */
/**
 *
 * 视图操作类
 * 只负责编译和渲染，不保存缓存内容
 * 也不涉及具体某个模板目录
 * @package HeXi
 * @author  FuXiaoHei <fuxiaohei@hexiaz.com>
 *
 */

class View {

    /**
     * 获取视图对象
     * @return bool|View
     */
    public static function create() {
        return Register::create('View', 'HeXi.View');
    }

    /**
     * 视图需要的和数据
     * @var array
     */
    private $data = array();

    /**
     * 设置视图的数据
     * @param string $name
     * @param mixed  $value
     */
    public function __set($name, $value) {
        $this->data[$name] = $value;
    }

    /**
     * 取消视图的数据
     * @param string $name
     */
    public function __unset($name) {
        unset($this->data[$name]);
    }

    /**
     * 按照数据添加视图数据
     * 会替换原有的值
     * @param array $data
     * @return View
     */
    public function data(array $data) {
        $this->data = array_merge($this->data, $data);
        return $this;
    }

    /**
     * 引用模板
     * @var array
     */
    private $imported = array();

    /**
     * 引入模板
     * @param string $name   名称
     * @param string $string 字符串，最好是编译好的
     * @return View
     */
    public function import($name, $string) {
        $this->imported[$name] = $string;
        return $this;
    }

    /**
     * 编译模板
     * @param string $template
     * @return string
     */
    public function compile($template) {
        $file = Register::command(Config::get('app.view.command') . '.' . $template) . '.' . Config::get('app.view.suffix');
        if (!is_file($file)) {
            Error::stop('无法编译视图 "' . $template . '"');
        }
        $string = file_get_contents($file);
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
     * 渲染模板
     * @param string $template
     * @return string
     */
    public function render($template) {
        $string = $this->compile($template);
        foreach ($this->imported as $import => $temp) {
            $string = str_replace('<!--' . $import . '-->', $temp, $string);
        }
        ob_start();
        extract($this->data);
        eval('?>' . $string);
        $string = ob_get_contents();
        ob_end_clean();
        return $string;
    }


}
