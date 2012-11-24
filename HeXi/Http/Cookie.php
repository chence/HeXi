<?php
/**
 *
 * HeXi 快速开发框架
 *
 *
 * @copyright Copyright (c) 2012 <hexiaz.com>
 * @author    : FuXiaoHei <fuxiaohei@hexiaz.com>
 * @create: 12-11-23 - 下午7:47
 * @link      : http://hexiaz.com
 *
 *
 */
/**
 *
 * Cookie的操作库类
 * @package Http
 * @author  FuXiaoHei <fuxiaohei@hexiaz.com>
 *
 */

class Cookie {

    /**
     * 设置Cookie的值
     * @param string      $key
     * @param string      $value
     * @param int         $expire
     * @param null|string $path
     * @param null|string $domain
     */
    public static function set($key, $value, $expire, $path = null, $domain = null) {
        setcookie($key, $value, NOW + $expire, $path, $domain);
        $_COOKIE[$key] = $value;
    }

    /**
     * 获取Cookie的值
     * @param string $key
     * @return mixed
     */
    public static function get($key) {
        return $_COOKIE[$key];
    }

    /**
     * 获取所有Cookie的值
     * @return array
     */
    public static function all() {
        return $_COOKIE;
    }

    /**
     * 清空Cookie的值
     */
    public static function clear() {
        foreach ($_COOKIE as $key => $value) {
            setcookie($key, null, NOW - 3600);
        }
        $_COOKIE = array();
    }

    /**
     * 删除Cookie的值
     * @param string $key
     */
    public static function remove($key) {
        setcookie($key, null, NOW - 3600);
        unset($_COOKIE[$key]);
    }

    /**
     * 创建很长时间保存的Cookie
     * @param string $key
     * @param string $value
     */
    public static function forever($key, $value) {
        setcookie($key, $value, NOW + 10 * 365 * 24 * 3600);
        $_COOKIE[$key] = $value;
    }
}
