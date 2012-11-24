<?php
/**
 *
 * HeXi 快速开发框架
 *
 *
 * @copyright Copyright (c) 2012 <hexiaz.com>
 * @author    : FuXiaoHei <fuxiaohei@hexiaz.com>
 * @create: 12-11-23 - 下午7:41
 * @link      : http://hexiaz.com
 *
 *
 */
/**
 *
 * 文件和库类的注册类
 * @package Core
 * @author  FuXiaoHei <fuxiaohei@hexiaz.com>
 *
 */

class Register {

    /**
     * 已经引入的文件
     * 格式：命令/对应文件
     * @var array
     */
    private static $imported = array();

    /**
     * 已经实例化的对象
     * 格式：命令/对象
     * @var array
     */
    private static $instanced = array();

    /**
     * 解析命令
     * @param string $cmd
     * @return string 返回命令对应的绝对地址，不带后缀名
     */
    public static function cmd($cmd) {
        #寻找加载常量
        $dirName = substr($cmd, 0, strpos($cmd, '.'));
        if (!defined($dirName . 'Dir')) {
            Error::stop('无法解析加载命令 "' . $cmd . '"');
        }
        #转化为绝对地址
        $file = str_replace(array( $dirName . '.', '.' ), array( constant($dirName . 'Dir'), DS ), $cmd);
        return $file;
    }

    /**
     * 加载命令对应的文件
     * @param string $cmd
     * @param bool   $stop 阻止程序运行
     * @return bool
     */
    public static function import($cmd, $stop = true) {
        if (!self::isImport($cmd)) {
            $file = self::cmd($cmd) . '.php';
            if (!is_file($file)) {
                if (!$stop) {
                    return false;
                }
                Error::stop('无法加载文件 "' . $file . '"');
            }
            require $file;
            self::$imported[$cmd] = $file;
        }
        return true;
    }

    /**
     * 生成命令对应的对象
     * @param string $className
     * @param string $cmd  命令并带有对象名称
     * @param bool   $stop 阻止程序运行
     * @return object
     */
    public static function create($className, $cmd, $stop = true) {
        if (!self::$instanced[$cmd]) {
            $key = false;
            $realCmd = $cmd;
            if (strstr($cmd, ':')) {
                $key = substr($cmd, stripos($cmd, ':') + 1);
                $realCmd = substr($cmd, 0, stripos($cmd, ':'));
            }
            self::import($realCmd,$stop);
            if (!class_exists($className, false)) {
                if (!$stop) {
                    return false;
                }
                Error::stop('无法找到库类 "' . $className . '"');
            }
            $object = $key === false ? new $className() : new $className($key);
            self::$instanced[$cmd] = $object;
        }
        return self::$instanced[$cmd];
    }

    /**
     * 获取已经引入的命令
     * 只是命令没有文件
     * @return array
     */
    public static function imported() {
        return array_keys(self::$imported);
    }

    /**
     * 判断是否引入了有个命令
     * @param string $cmd
     * @return bool
     */
    public static function isImport($cmd) {
        return array_key_exists($cmd, self::$imported);
    }
}
