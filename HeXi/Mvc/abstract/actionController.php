<?php
/**
 *
 * HeXi 快速开发框架
 *
 *
 * @copyright Copyright (c) 2012 <hexiaz.com>
 * @author    : FuXiaoHei <fuxiaohei@hexiaz.com>
 * @create: 12-11-23 - 下午7:51
 * @link      : http://hexiaz.com
 *
 *
 */
/**
 * Action操作的控制器基类
 * @package Mvc
 * @author  FuXiaoHei <fuxiaohei@hexiaz.com>
 *
 */

abstract class HeXiActionController {

    /**
     * 加载某个布局用的视图文件
     * @param string $view
     * @param array  $data
     * @return HeXiActionController
     */
    protected function import($view, $data = array()) {
        Action::import($view, $data);
        return $this;
    }

    /**
     * 运行
     * @return string
     */
    protected function run() {
        return Action::run();
    }

    /**
     * 设置返回类的返回
     * @param string $text
     * @param int    $status
     * @param string $contentType
     * @param array  $header
     * @return Response
     */
    public function response($text, $status = 200, $contentType = 'text/html', $header = array()) {
        return Response::create()
            ->status($status)
            ->contentType($contentType)
            ->body($text)
            ->header($header);
    }

}
