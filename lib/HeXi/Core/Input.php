<?php

/**
 *
 * 输入数据处理类
 * 负责input数据，cookie和session数据的处理
 *
 */
class Input {

    /**
     * 会话销毁
     */
    const SESSION_DESTROY = '50df0ea1ad050';

    /**
     * 会话提交
     */
    const SESSION_COMMIT = '50df0ec3c1090';

    /**
     * 会话值删除
     */
    const SESSION_DELETE = '50df0eda80b49';

    /**
     * 单例对象
     * @var Input
     */
    private static $self;

    /**
     * 获取单例
     * @return Input
     */
    public static function instance() {
        return !self::$self ? self::$self = new self() : self::$self;
    }

    /**
     * 获取param
     * @param bool|string $key
     * @return mixed
     */
    public function param($key = true) {
        return $key === true ? $GLOBALS['param'] : $GLOBALS['param'][$key];
    }

    /**
     * 获取GET
     * @param bool|string $key
     * @return array|string
     */
    public function get($key = true) {
        if (func_num_args() > 1) {
            $arg = func_get_args();
            $arg = array_flip($arg);
            return array_intersect_key($_GET, $arg);
        }
        return $key === true ? $_GET : $_GET[$key];
    }

    /**
     * 获取POST
     * @param bool|string $key
     * @return array|string
     */
    public function post($key = true) {
        if (func_num_args() > 1) {
            $arg = func_get_args();
            $arg = array_flip($arg);
            return array_intersect_key($_POST, $arg);
        }
        return $key === true ? $_POST : $_POST[$key];
    }

    /**
     * 添加和获取Cookie
     * @param bool|string   $name
     * @param null|mixed    $value
     * @param int           $expire
     * @param string        $path
     * @param null|string   $domain
     * @param bool          $secure
     * @param bool          $httpOnly
     * @return Input|array|string
     */
    public function cookie($name = true, $value = null, $expire = 3600, $path = '/', $domain = null, $secure = false,
                           $httpOnly = false) {
        if ($value === null) {
            return $name === true ? $_COOKIE : $_COOKIE[$name];
        }
        setcookie($name, $value, time() + $expire, $path, $domain, $secure, $httpOnly);
        $_COOKIE[$name] = $value;
        return $this;
    }

    /**
     * 添加和获取Session
     * @param bool|string $key
     * @param null|string $value
     * @return Input|string|array
     */
    public function session($key = true, $value = null) {
        if ($_SESSION === null) {
            session_start();
        }
        if ($value === null) {
            if ($key === true) {
                return $_SESSION;
            }
            if ($key === self::SESSION_DESTROY) {
                session_destroy();
                return $this;
            }
            if ($key === self::SESSION_COMMIT) {
                session_commit();
                return $this;
            }
            return eval('return $_SESSION["' . str_replace('.', '"]["', $key) . '"];');
        }
        if ($value === self::SESSION_DELETE) {
            eval('unset($_SESSION["' . str_replace('.', '"]["', $key) . '"]);');
            return $this;
        }
        eval('$_SESSION["' . str_replace('.', '"]["', $key) . '"] = $value;');
        return $this;
    }


}
