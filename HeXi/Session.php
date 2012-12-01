<?php
/**
 *
 * HeXi 快速开发框架
 *
 *
 * @copyright Copyright (c) 2012 <hexiaz.com>
 * @author    : FuXiaoHei <fuxiaohei@hexiaz.com>
 * @create: 12-11-29 - 下午10:29
 * @link      : http://hexiaz.com
 *
 *
 */
/**
 * Session操作类
 * @package HeXi
 * @author  FuXiaoHei <fuxiaohei@hexiaz.com>
 * @todo Session驱动
 */

class Session {

    /**
     * Session启动
     * @param bool|int $expire
     */
    public static function start($expire = true) {
        session_cache_limiter('nocache');
        if (is_numeric($expire)) {
            session_set_cookie_params($expire);
        }
        session_start();
    }

    /**
     * 销毁Session
     */
    public static function destroy() {
        session::destroy();
    }

    /**
     * 提交Session
     */
    public static function commit() {
        session::commit();
    }

    /**
     * 获取id
     * @param bool $new 是否重新生成
     * @return string
     */
    public static function id($new = false) {
        if ($new) {
            session_regenerate_id();
        }
        return session_id();
    }

    /**
     * 获取Session的值
     * @param string $key
     * @return mixed
     */
    public static function get($key) {
        if ($key === true) {
            return self::all();
        }
        return eval('return $_SESSION["' . str_replace('.', '"]["', $key) . '"];');
    }

    /**
     * 设置Session的值
     * @param string $key
     * @param mixed $value
     */
    public static function set($key, $value) {
        eval('$_SESSION["' . str_replace('.', '"]["', $key) . '"] = $value;');
    }

    /**
     * 返回Session数组
     * @return array
     */
    public static function all() {
        return $_SESSION;
    }
}
