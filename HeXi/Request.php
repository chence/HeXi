<?php
/**
 *
 * HeXi 快速开发框架
 *
 *
 * @copyright Copyright (c) 2012 <hexiaz.com>
 * @author    : FuXiaoHei <fuxiaohei@hexiaz.com>
 * @create: 12-11-28 - 下午9:21
 * @link      : http://hexiaz.com
 *
 *
 */
/**
 *
 * @package HeXi.Request
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

    //------------------------------------------------------

    /**
     * 获取所有请求数据
     * @return array
     */
    public static function all() {
        return $_REQUEST;
    }

    /**
     * 请求数据取值
     * @param string $value
     * @param string $rule
     * @return array|bool|float|int|object|string
     */
    private static function value($value, $rule) {
        if (function_exists($rule)) {
            return $rule($value);
        }
        switch ($rule) {
            case 'int':
                return (int)$value;
            case 'bool':
                return (bool)$value;
            case 'array':
                return (array)$value;
            case 'object':
                return (object)$value;
            case 'float':
                return (float)$value;
            default:
                return $value;
        }
    }

    /**
     * 获取请求数据
     * @param string $key
     * @return array|bool|float|int|object|string
     */
    public static function get($key) {
        if (strstr($key, ':')) {
            $key = explode(':', $key);
            return self::value($_REQUEST[$key[0]], $key[1]);
        }
        return $_REQUEST[$key];
    }

    /**
     * 是否存在请求数据
     * @param string $key
     * @return bool
     */
    public static function has($key) {
        return isset($_REQUEST[$key]);
    }

    /**
     * 打包数据成一个数组
     * @return array
     */
    public static function pack() {
        if (func_num_args() > 1) {
            $args = func_get_args();
        } else {
            $args = func_get_arg(0);
            $args = explode(',', $args);
        }
        $data = array();
        foreach ($args as $key) {
            $value = self::get($key);
            $key = strstr($key, ':') ? substr($key, 0, stripos($key, ':')) : $key;
            $data[$key] = $value;
        }
        return $data;
    }

    /**
     * 获取除了一些键值的数据打包数组
     * @return array
     */
    public static function except() {
        if (func_num_args() > 1) {
            $args = func_get_args();
        } else {
            $args = func_get_arg(0);
            $args = explode(',', $args);
        }
        $data = self::all();
        foreach ($args as $key) {
            unset($data[$key]);
        }
        return $data;
    }

    /**
     * 替换数据到全局数组中
     */
    public static function replace() {
        if (func_num_args() > 1) {
            $args = func_get_args();
        } else {
            $args = func_get_arg(0);
            $args = explode(',', $args);
        }
        foreach ($args as $key) {
            $value = self::get($key);
            $key = strstr($key, ':') ? substr($key, 0, stripos($key, ':')) : $key;
            $_REQUEST[$key] = $value;
        }
    }

    //--------------------------------------------------

    /**
     * 设置Cookie信息
     * @param string        $name cookie名称
     * @param null|string   $value 键值
     * @param int|string    $expire 过期时间，如果是负数，就是删除Cookie
     * @param string        $path 有效地址
     * @param null|string   $domain 有效域名
     * @return array|string
     */
    public static function cookie($name, $value = null, $expire = 3600, $path = '/', $domain = null) {
        if ($value === null && $expire > 0) {
            return $_COOKIE[$name];
        }
        if ($name === true) {
            return $_COOKIE;
        }
        if ($expire == 'forever') {
            $expire = 3600 * 24 * 365 * 100;
        }
        setcookie($name, $value, time() + $expire, $path, $domain);
        return $value;
    }
}
