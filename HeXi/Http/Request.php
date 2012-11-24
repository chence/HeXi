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
 *
 * 请求类的操作类
 * @package Http
 * @author  FuXiaoHei <fuxiaohei@hexiaz.com>
 *
 */

class Request {

    /**
     * 获取ip地址
     * @return string
     */
    public static function ip() {
        if ($_SERVER['HTTP_CLIENT_IP']) {
            return $_SERVER['HTTP_CLIENT_IP'];
        }
        if ($_SERVER['HTTP_X_FORWARDED_FOR']) {
            $ip = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            return isset($ip[0]) ? trim($ip[0]) : '';
        }
        return $_SERVER['REMOTE_ADDR'];
    }

    /**
     * 获取主机名称
     * @return string
     */
    public static function host() {
        if ($_SERVER['HTTP_X_FORWARDED_FOR']) {
            $host = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            $host = trim($host[count($host) - 1]);
        } else {
            $host = $_SERVER['HTTP_HOST'];
            if (!$host) {
                $host = $_SERVER['SERVER_NAME'];
            }
        }
        return $host;
    }

    /**
     * 获取当前访问的url
     * @return string
     */
    public static function url() {
        return !$_SERVER['REQUEST_URI'] ? $_SERVER['PATH_INFO'] : $_SERVER['REQUEST_URI'];
    }

    /**
     * 获取访问url的后缀名
     * @return string
     */
    public static function suffix() {
        $string = parse_url(self::url(), PHP_URL_PATH);
        return pathinfo($string, PATHINFO_EXTENSION);
    }

    /**
     * 获取请求客户端标识
     * @return string
     */
    public static function agent() {
        return self::server('http_user_agent');
    }

    /**
     * 获取请求方式
     * 或者判断是否是某个请求方式
     * @param null|string $method
     * @return bool|string
     */
    public static function method($method = null) {
        if ($method) {
            return strtoupper($method) == strtoupper($_SERVER['REQUEST_METHOD']);
        }
        return strtoupper($_SERVER['REQUEST_METHOD']);
    }

    /**
     * 是否是Ajax请求
     * @return bool
     */
    public static function ajax() {
        return 'XMLHttpRequest' == self::server('Http-X-Requested-With');
    }

    /**
     * 是否是加密链接
     * @return bool
     */
    public static function secure() {
        return (self::server('HTTPS') == 'on' || self::server('http_ssl_https') == 'on');
    }

    /**
     * 获取HTTP头信息
     * @param string $key
     * @return mixed
     */
    public static function header($key) {
        return self::server('http_' . $key);
    }

    /**
     * 获取SERVER数组的值
     * @param string $key
     * @return mixed
     */
    public static function server($key) {
        return $_SERVER[strtoupper($key)];
    }

    /**
     * 获取环境变量的值
     * @param string $key
     * @return string
     */
    public static function env($key) {
        return getenv($key);
    }
}
