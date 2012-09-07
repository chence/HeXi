<?php

/**
 * Cookie的类
 * @author FuXiaoHei
 */
class Cookie {

    /**
     * 单例对象
     * @var Cookie
     */
    private static $obj;

    /**
     * 获取单例
     * @return Cookie
     */
    public static function init() {
        return !self::$obj ? self::$obj = new Cookie() : self::$obj;
    }

    /**
     * 私有的初始化方法
     */
    private function __construct() {
        $this->reset();
    }

    /**
     * Cookie有效地址
     * @var string
     */
    public $path;

    /**
     * 有效域名
     * @var string
     */
    public $domain;

    /**
     * 过期时间
     * @var int
     */
    public $expire;

    /**
     * 重置配置
     */
    public function reset() {
        $this->path = config('cookie.path');
        $this->domain = config('cookie.domain');
        $this->expire = config('cookie.expire');
    }

    /**
     * 获取Cookie
     * @param string $name
     * @return mixed
     */
    public function get($name) {
        if($name === true){
            return $_COOKIE;
        }
        return $_COOKIE[$name];
    }

    /**
     * 写入Cookie
     * @param string $name
     * @param string $value
     */
    public function set($name, $value) {
        setcookie($name, $value, NOW + $this->expire, $this->path, $this->domain);
        $_COOKIE[$name] = $value;
    }

    /**
     * 是否存在cookie
     * @param string $name
     * @return bool
     */
    public function has($name) {
        return isset($_COOKIE[$name]);
    }

    /**
     * 删除Cookie
     * @param string $name
     */
    public function del($name) {
        if($name === true){
            unset($_COOKIE);
            return;
        }
        setcookie($name, null, NOW - 3600);
        unset($_COOKIE[$name]);
    }

    /**
     * 设置永久cookie，10年
     * @param string $name
     * @param string $value
     */
    public function forever($name, $value) {
        setcookie($name, $value, NOW + 3600 * 24 * 365 * 10, $this->path, $this->domain);
    }
}
