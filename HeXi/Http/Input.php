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
 * 输入数据的操作类
 * @package Http
 * @author  FuXiaoHei <fuxiaohei@hexiaz.com>
 *
 */

class Input {

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
            $value = Input::get($key);
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
            $value = Input::get($key);
            $key = strstr($key, ':') ? substr($key, 0, stripos($key, ':')) : $key;
            $_REQUEST[$key] = $value;
        }
    }

}
