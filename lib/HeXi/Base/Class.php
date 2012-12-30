<?php
/**
 * Created by JetBrains PhpStorm.
 * User: FuXiaoHei
 * Date: 12-12-29
 * Time: 下午6:00
 * To change this template use File | Settings | File Templates.
 */
class Base_Class {

    protected $config;

    public function __construct() {
        $this->config = & HeXi::$config;
    }

    protected function config($config) {
        HeXi::loadConfig($config);
        return $this->config[$config];
    }

    protected function stop($message, $status = 500) {
        Error::stop($message, $status);
    }

    protected function import($className) {
        return Hexi::import($className, false);
    }

    protected function instance($className, $key = null) {
        return HeXi::instance($className, $key);
    }


}
