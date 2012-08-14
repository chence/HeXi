<?php
/**
 * web处理类
 * @author fuxiaohei
 * @copyright 12-8-14 fuxiaohei
 *
 */
class web {

    /**
     * 单例
     * @var web
     */
    private static $web;

    /**
     * 单例
     * @static
     * @return web
     */
    public static function init() {
        if (!self::$web instanceof web) {
            self::$web = new web();
        }
        return self::$web;
    }

    /**
     * 私有初始化
     */
    private function __construct() {
        if (function_exists('get_magic_quotes_gpc') && get_magic_quotes_gpc()) {
            $_GET = $this->_strip($_GET);
            $_POST = $this->_strip($_POST);
            $_REQUEST = $this->_strip($_REQUEST);
            $_COOKIE = $this->_strip($_COOKIE);
        }
    }

    /**
     * 过滤多余的引号
     * @param array|string $data
     * @return array|string
     */
    private function _strip(&$data) {
        return is_array($data) ? array_map(array($this, '_strip'), $data) : stripslashes($data);
    }


    /**
     * 禁止克隆
     * @throws Exception
     */
    public function __clone() {
        throw new Exception('web不能克隆');
    }

    /**
     * 获取request
     * @param string $name
     * @return mixed
     */
    public function input($name) {
        return $_REQUEST[$name];
    }

    /**
     * 发送头信息
     * @param string $name
     * @param string $value
     */
    public function header($name, $value) {
        header($name . ':' . $value);
    }

    /**
     * cookie
     * @param string $name
     * @param string $value
     * @param int $expire
     * @param string $path
     * @param string $domain
     * @return bool
     */
    public function cookie($name, $value, $expire = 86400, $path = '/', $domain = null) {
        if ($value === null) {
            return $_COOKIE[$name];
        }
        return setcookie($name, $value, time() + $expire, $path, $domain);
    }

    /**
     * 清除cookie
     * @param string $name
     * @return mixed
     */
    public function cookie_clear($name) {
        if ($name == true) {
            $_COOKIE = array();
            return;
        }
        setcookie($name, null, time() - 3600);
        unset($_COOKIE[$name]);
    }

    /**
     * session
     * @param string $name
     * @param mixed $value
     * @return array
     */
    public function session($name, $value = null) {
        if ($value === null) {
            if ($name === true) {
                return $_SESSION;
            }
            return $_SESSION[$name];
        }
        $_SESSION[$name] = $value;
        return $value;
    }

    /**
     * 清空session
     */
    public function session_clear() {
        unset($_SESSION);
    }

    /**
     * 开启session
     */
    public function session_start() {
        session_start();
    }


    /**
     * 提交session
     */
    public function session_commit() {
        session_write_close();
    }

    /**
     * 销毁session
     */
    public function session_destroy() {
        session_destroy();
    }

    /**
     * 获取server信息
     * @param string $name
     * @return mixed
     */
    public function server($name) {
        return $_SERVER[strtoupper($name)];
    }
}
