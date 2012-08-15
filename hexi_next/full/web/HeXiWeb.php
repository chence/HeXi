<?php
/**
 * Created by JetBrains PhpStorm.
 * User: fuxiaohei
 * Date: 12-8-11
 * Time: 下午4:44
 * To change this template use File | Settings | File Templates.
 */
class HeXiWeb {


    /**
     * 初始化
     * @static
     *
     */
    public static function init() {
        #过滤请求数据
        if (function_exists('get_magic_quotes_gpc') && get_magic_quotes_gpc()) {
            if (isset($_GET)) {
                $_GET = self::_stripSlashes($_GET);
            }
            if (isset($_POST)) {
                $_POST = self::_stripSlashes($_POST);
            }
            if (isset($_REQUEST)) {
                $_REQUEST = self::_stripSlashes($_REQUEST);
            }
            if (isset($_COOKIE)) {
                $_COOKIE = self::_stripSlashes($_COOKIE);
            }
        }
        #初始化Cookie
        self::resetCookieConfig();
        #初始化Session
        self::$sessionConfig['auto'] = HeXiConfig::get('session', 'auto');
        self::$sessionConfig['prefix'] = HeXiConfig::get('session', 'prefix');
        if (self::$sessionConfig['auto']) {
            self::SessionStart();
        }
        #初始化response
        self::$response = array(
            'content-type'=> 'text/html',
            'charset'     => 'utf-8',
            'status'      => 200,
            'body'        => '',
            'file'        => false,
            'header'      => array(
                'X-Powered-By'=> 'HeXi Framework'
            )
        );
    }

    /**
     * 获取GET信息
     * @static
     * @param bool|string $name
     * @return array|string|null
     */
    public static function Get($name = true) {
        if ($name == true) {
            return $_GET;
        }
        return (isset($_GET[$name])) ? $_GET[$name] : null;
    }

    /**
     * 获取POST信息
     * @static
     * @param bool|string $name
     * @return array|string|null
     */
    public static function Post($name = true) {
        if ($name == true) {
            return $_POST;
        }
        return (isset($_POST[$name])) ? $_POST[$name] : null;
    }

    /**
     * 获取REQUEST数据
     * @static
     * @param bool|string $name
     * @return array|string|null
     */
    public static function Request($name = true) {
        if ($name == true) {
            return $_REQUEST;
        }
        return (isset($_REQUEST[$name])) ? $_REQUEST[$name] : null;
    }

    /**
     * 过滤多余的引号
     * @static
     * @param array|string $data
     * @return array|string
     */
    private static function _stripSlashes(&$data) {
        return is_array($data) ? array_map('self::_stripSlashes', $data) : stripslashes($data);
    }

    /**
     * Cookie的基本配置
     * @var array
     */
    private static $cookieConfig = array();

    /**
     * 修改Cookie配置
     * @static
     * @param string $prefix
     * @param string $path
     * @param string $domain
     */
    public static function CookieConfig($prefix, $path, $domain) {
        self::$cookieConfig['prefix'] = $prefix;
        self::$cookieConfig['path'] = $path;
        self::$cookieConfig['domain'] = $domain;
    }

    /**
     * 重置Cookie配置
     * @static
     *
     */
    public static function resetCookieConfig() {
        self::$cookieConfig['domain'] = null;
        self::$cookieConfig['prefix'] = HeXiConfig::get('cookie', 'prefix');
        self::$cookieConfig['path'] = HeXiConfig::get('cookie', 'path');
        self::$cookieConfig['expire'] = HeXiConfig::get('cookie', 'expire');
    }

    /**
     * 写入和读取Cookie
     * @static
     * @param string $name
     * @param null|string $value
     * @param int $expire
     * @return array|bool
     */
    public static function Cookie($name, $value = null, $expire = 0) {
        if ($value === null) {
            if ($name === true) {
                return $_COOKIE;
            }
            return $_COOKIE[self::$cookieConfig['prefix'] . $name];
        }
        if ($expire === 0) {
            $expire = self::$cookieConfig['expire'];
        }
        if ($value === false) {
            unset($_COOKIE[self::$cookieConfig['prefix'] . $name]);
        }
        setcookie(self::$cookieConfig['prefix'] . $name, $value, time() + $expire, self::$cookieConfig['path'], self::$cookieConfig['domain']);
        return true;
    }

    /**
     * Sesion的配置
     * @var array
     */
    protected static $sessionConfig = array();

    /**
     * 开启Session
     * @static
     *
     */
    public static function SessionStart() {
        session_start();
    }

    /**
     * 获取Session的id
     * @static
     * @return string
     */
    public static function SessionId() {
        return session_id();
    }

    /**
     * 设置和获取Session
     * @static
     * @param string $name
     * @param null|mixed $value
     * @return array|mixed|bool
     */
    public static function Session($name, $value = null) {
        if ($value === null) {
            if ($name === true) {
                return $_SESSION;
            }
            return $_SESSION[self::$sessionConfig['prefix'] . $name];
        }
        if ($value === false) {
            unset($_SESSION[self::$sessionConfig['prefix'] . $name]);
        }
        $_SESSION[self::$sessionConfig['prefix'] . $name] = $value;
        return true;
    }

    /**
     * 停止写入Session
     * @static
     *
     */
    public static function SessionCommit() {
        session_write_close();
    }

    /**
     * 销毁Session
     * @static
     *
     */
    public static function SessionDestroy() {
        session_destroy();
    }

    //-------------------------------

    /**
     * 获取SERVER内容
     * @static
     * @param string $name
     * @return null|mixed
     */
    public static function Server($name) {
        return (isset($_SERVER[$name])) ? $_SERVER[$name] : null;
    }

    /**
     * 是否GET请求
     * @static
     * @return bool
     */
    public static function isGet() {
        return strtoupper(self::Server('REQUEST_METHOD')) == "GET";
    }

    /**
     * 是否POST请求
     * @static
     * @return bool
     */
    public static function isPost() {
        return strtoupper(self::Server('REQUEST_METHOD')) == "POST";
    }

    /**
     * 是否Ajax请求
     * @static
     * @return bool
     */
    public static function isAjax() {
        return !strcasecmp(self::Server('HTTP_X_REQUESTED_WITH'), 'XMLHttpRequest');
    }

    /**
     * 获取主机名
     * @static
     * @return string
     */
    public static function getHost() {
        return self::Server('SERVER_NAME');
    }

    /**
     * 获取远程ip地址
     * @static
     * @return string
     */
    public static function getIp() {
        return self::Server('REMOTE_ADDR');
    }

    /**
     * 获取上一次访问页面
     * @static
     * @return string
     */
    public static function getRefer() {
        return self::Server('HTTP_REFERER');
    }

    /**
     * 获取客户端信息
     * @static
     * @return string
     */
    public static function getAgent() {
        return self::Server('HTTP_USER_AGENT');
    }

    //---------------------------------------------------

    /**
     * 返回请求的数据
     * @var array
     */
    protected static $response = array();

    /**
     * 是否已发送头信息
     * @var bool
     */
    protected static $hasResponse = false;

    /**
     * HTTP状态
     * @var array
     */
    protected static $httpStatus = array(
        505 => 'http version not supported',
        504 => 'gateway timeout',
        503 => 'service unavailable',
        502 => 'bad gateway',
        501 => 'not implemented',
        500 => 'internal server error',
        417 => 'expectation failed',
        416 => 'requested range not satisfiable',
        415 => 'unsupported media type',
        414 => 'request uri too long',
        413 => 'request entity too large',
        412 => 'precondition failed',
        411 => 'length required',
        410 => 'gone',
        409 => 'conflict',
        408 => 'request timeout',
        407 => 'proxy authentication required',
        406 => 'not acceptable',
        405 => 'method not allowed',
        404 => 'not found',
        403 => 'forbidden',
        402 => 'payment required',
        401 => 'unauthorized',
        400 => 'bad request',
        300 => 'multiple choices',
        301 => 'moved permanently',
        302 => 'moved temporarily',
        303 => 'see other',
        304 => 'not modified',
        305 => 'use proxy',
        307 => 'temporary redirect',
        100 => 'continue',
        101 => 'witching protocols',
        200 => 'ok',
        201 => 'created',
        202 => 'accepted',
        203 => 'non authoritative information',
        204 => 'no content',
        205 => 'reset content',
        206 => 'partial content'
    );

    /**
     * 发送文档类型和字符集
     * @static
     * @param string $type
     * @param string $charset
     */
    public static function sendContentType($type, $charset = 'utf-8') {
        self::$response['content-type'] = $type;
        self::$response['charset'] = $charset;
    }

    /**
     * 发送状态码
     * @static
     * @param int $code
     */
    public static function sendStatus($code) {
        self::$response['status'] = $code;
    }

    /**
     * 文本内容
     * @static
     * @param string $body
     */
    public static function sendBody($body) {
        self::$response['body'] = $body;
    }

    /**
     * 发送头信息
     * @static
     * @param string $name
     * @param string $value
     */
    public static function sendHeader($name, $value) {
        self::$response['header'][$name] = $value;
    }

    /**
     * 发送跳转信息
     * @static
     * @param string $url
     */
    public static function sendRedirect($url) {
        self::$response['header']['Location'] = $url;
        self::sendStatus(302);
    }

    /**
     * 发送缓存头
     * @static
     * @param int $expire
     */
    public static function sendCacheHeader($expire) {
        self::sendHeader('Last-Modified', gmdate('r'));
        self::sendHeader('Expires:"', gmdate('r', time() + $expire));
        self::sendHeader('Cache-Control', 'max-age=' . $expire);
    }

    /**
     * 发送文件下载
     * @static
     * @param string $file
     * @throws HeXiWebException
     */
    public static function sendDownloadHeader($file) {
        if (!is_file($file)) {
            throw new HeXiWebException('Download File "' . $file . '" is invalid !');
        }
        $filename = basename($file);
        self::sendContentType("application/octet-stream");
        $ua = $_SERVER["HTTP_USER_AGENT"];
        $encodedFilename = urlencode($filename);
        $encodedFilename = str_replace("+", "%20", $encodedFilename);
        if (preg_match("/MSIE/", $ua)) {
            self::sendHeader('Content-Disposition', 'attachment; filename="' . $encodedFilename . '"');
        } else if (preg_match("/Firefox/", $ua)) {
            self::sendHeader("Content-Disposition", "attachment; filename*=\"utf8''" . $filename . '"');
        } else {
            self::sendHeader('Content-Disposition', 'attachment; filename="' . $filename . '"');
        }
        self::sendHeader('Content-Disposition', 'attachment; filename="' . $filename . '"');
        self::sendHeader("Content-Length", filesize($file));
        self::$response['file'] = $file;
    }

    /**
     * 发送最后头信息
     * @static
     * @return mixed
     */
    public static function endResponse() {
        #如果已经发送了，就不发送了。。头信息只发送一次
        if (self::$hasResponse) {
            return;
        }
        header('HTTP/1.1 ' . self::$response['status'] . ' ' . ucwords(self::$httpStatus[self::$response['status']]));
        header('Status: ' . self::$response['status'] . ' ' . ucwords(self::$httpStatus[self::$response['status']]));
        header('Content-type:' . self::$response['content-type'] . ';charset=' . self::$response['charset']);
        foreach (self::$response['header'] as $key=> $header) {
            header($key . ':' . $header);
        }
        if (self::$response['file']) {
            readfile(self::$response['file']);
            return;
        }
        echo self::$response['body'];
        self::$hasResponse = true;
    }
}

/**
 * Web异常类
 */
class HeXiWebException extends HeXiException {

}
