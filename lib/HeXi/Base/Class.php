<?php

/**
 *
 * 抽象类的基类
 *
 */
class Base_Class {

    /**
     * 配置信息
     * @var array
     */
    protected $config;

    /**
     * 构造方法
     */
    public function __construct() {
        #引入全局配置
        $this->config = & HeXi::$config;
    }

    /**
     * 引入配置
     * 就是全局静态方法的别名
     * @param string $config
     * @return mixed
     */
    protected function config($config) {
        HeXi::loadConfig($config);
        return $this->config[$config];
    }

    /**
     * 暂停运行
     * 也就是全局暂停的别名
     * @param string $message
     * @param int $status
     */
    protected function stop($message, $status = 500) {
        Error::stop($message, $status);
    }

    /**
     * 引入库类
     * 全局引入的别名
     * @param  string $className
     * @return bool
     */
    protected function import($className) {
        return Hexi::import($className, false);
    }

    /**
     * 实例化一个库类
     * @param string $className
     * @param null|string $key
     * @return object
     */
    protected function instance($className, $key = null) {
        return HeXi::instance($className, $key);
    }

    /**
     * 调用任意类的方法
     * call_user_func的别名
     * @param string $call
     * @return mixed
     */
    protected function exec($call) {
        $args = func_get_args();
        array_shift($args);
        $call = explode('->', $call);
        return call_user_func_array(array($this->instance($call[0], $call[1])), $args);
    }

}
