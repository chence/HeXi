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
 * 一般控制器的基类
 * 执行一般控制器的操作
 * @package Mvc
 * @author  FuXiaoHei <fuxiaohei@hexiaz.com>
 *
 */

abstract class HeXiBaseController {

    /**
     * 执行模型方法
     * @param string $modelName
     * @param string $method
     * @param array $args
     * @return mixed
     */
    public function invoke($modelName,$method,$args = array()){
        return Model::invoke($modelName,$method,$args);
    }

    /**
     * 设置视图模板的返回
     * @param string $template
     * @param array  $data
     * @return View
     */
    public function template($template, $data = array()) {
        $dir = substr($template,0,stripos($template,'.'));
        return View::create($dir)
            ->template(str_replace($dir.'.','',$template))
            ->with($data);
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

    /**
     * 重定向
     * @param string $url
     * @return Response
     */
    public function redirect($url) {
        return Response::create()
            ->redirect($url);
    }

    /**
     * json返回的返回类
     * @param mixed $data
     * @return Response
     */
    public function ajaxReturn($data) {
        return $this->response(json_encode($data), 200, 'application/json');
    }
}
