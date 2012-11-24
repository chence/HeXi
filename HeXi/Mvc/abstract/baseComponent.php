<?php
/**
 *
 * HeXi 快速开发框架
 *
 *
 * @copyright Copyright (c) 2012 <hexiaz.com>
 * @author    : FuXiaoHei <fuxiaohei@hexiaz.com>
 * @create: 12-11-23 - 下午7:46
 * @link      : http://hexiaz.com
 *
 *
 */
/**
 * 组件类的基类
 * @package Mvc
 * @author  FuXiaoHei <fuxiaohei@hexiaz.com>
 *
 */

abstract class HeXiBaseComponent {

    /**
     * 组件类内部后视图的地址
     * @var string
     */
    protected $view;

    /**
     * 初始化方法
     */
    public function __construct() {
        $this->view = Config::get('app.component.cmd') . '.' . rtrim(get_class($this), 'Component') . '.view';
    }

    /**
     * 执行模型类操作
     * @param string $model
     * @param string $method
     * @param array  $args
     * @return mixed
     */
    protected function invoke($model, $method, $args = array()) {
        return Model::invoke($model, $method, $args);
    }

    /**
     * 渲染内部视图数据
     * @param string $cmd
     * @param array $data
     * @return string
     */
    protected function render($cmd, $data = array()) {
        $viewFile = Register::cmd($this->view . '.' . $cmd) . '.' . Config::get('app.component.suffix');
        if (!is_file($viewFile)) {
            Error::stop('无法找到组建视图 "' . $viewFile . '"');
        }
        return View::render(View::compile(file_get_contents($viewFile)), $data);
    }

}
