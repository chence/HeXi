<?php
/**
 *
 * HeXi 快速开发框架
 *
 *
 * @copyright Copyright (c) 2012 <hexiaz.com>
 * @author    : FuXiaoHei <fuxiaohei@hexiaz.com>
 * @create: 12-11-24 - 下午7:56
 * @link      : http://hexiaz.com
 *
 *
 */
/**
 * 命令模板处理类
 * 负责处理带有可执行命令的模板的操作
 * @package Core
 * @author  FuXiaoHei <fuxiaohei@hexiaz.com>
 *
 */

class Action {

    /**
     * 解析的视图模板
     * @var string
     */
    private static $view = '';

    /**
     * 视图模板基本内容
     * @var string
     */
    private static $layout = '';

    /**
     * 模板中命令解析结果
     * @var array
     */
    private static $result = array();

    /**
     * 模板中的命令
     * @var array
     */
    private static $actions = array();

    /**
     * 等待执行的命令
     * @var array
     */
    private static $wait = array();

    /**
     * 布局模板需要的数据
     * @var array
     */
    private static $data = array();

    /**
     * 引入布局模板
     * 及预备使用的数据
     * @param string $template
     * @param array $data
     */
    public static function import($template, $data = array()) {
        $viewFile = Config::get('app.view.cmd') . '.' . $template;
        self::$view = $viewFile;
        $viewFile = Register::cmd($viewFile) . '.' . Config::get('app.view.suffix');
        if (!is_file($viewFile)) {
            Error::stop('无法找到Action类操作模板 "' . $template . '"');
        }
        self::$layout = file_get_contents($viewFile);
        $pattern = '/<!--(view|component):(.*)-->/';
        if (preg_match_all($pattern, self::$layout, $matches)) {
            self::$actions = str_replace(array( '<!--', '-->' ), '', $matches[0]);
        }
        self::$wait = self::$actions;
        self::$data = $data;
    }

    /**
     * 运行布局模板中的命令
     * 并返回解析后的文本结果
     * @return string
     */
    public static function run() {
        foreach (self::$wait as $key => $action) {
            if (strpos($action, 'view:') === 0) {
                $viewCmd = substr(self::$view, 0, strrpos(self::$view, '.')) . '.' . ltrim($action, 'view:');
                $viewFile = Register::cmd($viewCmd) . '.' . Config::get('app.view.suffix');
                if (!is_file($viewFile)) {
                    self::$result[$action] = false;
                } else {
                    self::$result[$action] = View::compile(file_get_contents($viewFile));
                }
                unset(self::$wait[$key]);
                continue;
            }
            if (strpos($action, 'component:') === 0) {
                $com = explode('->', ltrim($action, 'component:'));
                self::$result[$action] = Component::invoke($com[0] . 'Component', $com[1]);
                unset(self::$wait[$key]);
                continue;
            }
        }
        return self::buffer();
    }

    /**
     * 拼接命令处理后的结果
     * @return string
     */
    private static function buffer() {
        $buffer = self::$layout;
        foreach (self::$actions as $action) {
            $result = self::$result[$action];
            if ($result === false || $result === null) {
                $buffer = str_replace('<!--' . $action . '-->', '', $buffer);
            } else {
                $buffer = str_replace('<!--' . $action . '-->', $result, $buffer);
            }
        }
        return View::render(View::compile($buffer), self::$data);
    }

    /**
     * 判断某个命令是否在等待执行
     * @param string $action
     * @return bool
     */
    public static function isWait($action) {
        return in_array($action, self::$wait);
    }

    /**
     * 移除某个命令
     * @param string $action
     */
    public static function remove($action){
        self::$wait = array_diff(self::$wait,array($action));
    }

}
