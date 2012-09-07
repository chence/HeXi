<?php
/**
 * Created by JetBrains PhpStorm.
 * User: FuXiaoHei
 * Date: 12-9-7
 * Time: 上午10:40
 */
/**
 * @author FuXiaoHei
 */
class Session {


    /**
     * 单例对象
     * @var Session
     */
    private static $obj;

    /**
     * 获取单例
     * @return Session
     */
    public static function init() {
        return !self::$obj ? self::$obj = new Session() : self::$obj;
    }

    /**
     * 私有的初始化方法
     */
    private function __construct() {
        if (config('session.auto')) {
            session_start();
        }
    }

    /**
     * 开启Session
     * @return Session
     */
    public function start(){
        session_start();
        return $this;
    }

    /**
     * 获取Session
     * @param string $name
     * @return mixed
     */
    public function get($name) {
        if ($name === true) {
            return $_SESSION;
        }
        return $_SESSION[$name];
    }

    /**
     * 写入Session
     * @param string $name
     * @param string $value
     */
    public function set($name, $value) {
        $_SESSION[$name] = $value;
    }

    /**
     * 是否存在Session
     * @param string $name
     * @return bool
     */
    public function has($name) {
        return isset($_SESSION[$name]);
    }

    /**
     * 删除Session
     * @param string $name
     */
    public function del($name) {
        if ($name === true) {
            unset($_SESSION);
            return;
        }
        unset($_SESSION[$name]);
    }

    /**
     * 提交Session，停止写入
     */
    public function commit() {
        session_commit();
    }

    /**
     * 销毁Session
     */
    public function destroy() {
        session_destroy();
    }
}
