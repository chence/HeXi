<?php
/**
 *
 * HeXi 快速开发框架
 *
 *
 * @copyright Copyright (c) 2012 <hexiaz.com>
 * @author    : FuXiaoHei <fuxiaohei@hexiaz.com>
 * @create: 12-11-28 - 下午6:01
 * @link      : http://hexiaz.com
 *
 *
 */
/**
 * 注册器类
 * 1.解析注册命令，获取对应绝对地址信息，不带后缀名
 * 2.注册文件引入
 * 3.注册对象实例
 * @package HeXi
 * @author  FuXiaoHei <fuxiaohei@hexiaz.com>
 *
 */

class Register {

    /**
     * 解析注册命令，获取注册命令对应绝对地址
     * @param string $string
     * @return string
     */
    public static function command($string) {
        $dirName = substr($string, 0, stripos($string, '.')) . 'Dir';
        if (!defined($dirName)) {
            Error::stop('无法解析注册命令的文件目录 "' . $dirName);
        }
        $fileUri = substr($string, stripos($string, '.') + 1);
        $file = constant($dirName) . str_replace('.', DIRECTORY_SEPARATOR, $fileUri);
        return $file;
    }

    /**
     * 已经添加的注册命令
     * @var array
     */
    private static $importCommand = array();

    /**
     * 引入文件
     * @param string $string
     * @param bool   $stop 是否停止运行
     * @return bool
     */
    public static function import($string, $stop = true) {
        if (!array_key_exists($string, self::$importCommand)) {
            $file = self::command($string) . '.php';
            if (!is_file($file)) {
                if (!$stop) {
                    return false;
                }
                Error::stop('无法加载注册命令 "' . $string . '" 对应文件');
            }
            require $file;
            self::$importCommand[$string] = $file;
        }
        return true;
    }

    /**
     * 已经引入的实例化对象
     * @var array
     */
    private static $objects = array();

    /**
     * 创建或使用实例化对象
     * @param string $objectName 对象名称
     * @param string $command    引入命令，如果有如Class:name的命令，即new $objectName("name");
     * @return mixed|bool
     */
    public static function create($objectName, $command = '') {
        if (self::$objects[$command] instanceof $objectName) {
            return self::$objects[$command];
        }
        $key = null;
        $realCommand = $command;
        if (strstr($command, ':')) {
            $key = substr($command, stripos($command, ':') + 1);
            $realCommand = substr($command, 0, strpos($command, ':'));
        }
        if (strlen($realCommand) > 0) {
            $res = self::import($realCommand, false);
            if (!$res) {
                return false;
            }
        }
        $object = !($key) ? new $objectName() : new $objectName($key);
        self::$objects[$command] = $object;
        return self::$objects[$command];
    }

    /**
     * 获取有所加载的命令
     * @return array
     */
    public static function imported() {
        return array_keys(self::$importCommand);
    }

}
