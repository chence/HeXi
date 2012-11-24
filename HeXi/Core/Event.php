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
 * 事件调用类
 * @package Core
 * @author  FuXiaoHei <fuxiaohei@hexiaz.com>
 *
 */

class Event {

    /**
     * 保存调用的事件
     * @var array
     */
    private static $events = array();

    /**
     * 保存呼叫调用的事件
     * @var array
     */
    private static $called = array();

    /**
     * 绑定某个事件
     * @param string $name
     * @param callable $call 可调用的操作，或者闭包函数
     */
    public static function bind($name, $call) {
        self::$events[$name][] = $call;
    }

    /**
     * 引入事件集合类
     * @param HeXiBaseEvent $eventObjectCmd
     */
    public static function import($eventObjectCmd) {
        $eventObjectCmd->install();
    }

    /**
     * 调用某个事件
     * @param string $name
     * @param array $args
     */
    public static function trigger($name, $args = array()) {
        self::$called[] = $name;
        $events = self::$events[$name];
        if ($events) {
            foreach ($events as $event) {
                if (!is_callable($event)) {
                    Error::stop('无法调用事件 "'.$name.'"的操作');
                }
                call_user_func_array($event, $args);
            }
        }
    }

    /**
     * 呼叫调用的事件
     * @return array
     */
    public static function called() {
        return array_unique(self::$called);
    }
}
