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
 * 模型类的基本类
 * @package Mvc
 * @author  FuXiaoHei <fuxiaohei@hexiaz.com>
 *
 */

abstract class HeXiBaseModel {

    /**
     * 数据库驱动类
     * @var HeXiDriver
     */
    protected $conn;

    /**
     * 公共的调用方法
     */
    public function __construct() {
        $name = Config::get('app.model.database');
        if ($name) {
            $this->conn = Database::connect($name);
        }
    }
}
