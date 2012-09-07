<?php

/**
 * 请求处理类
 * @author FuXiaoHei
 */
class Request {

    /**
     * 单例对象
     * @var Request
     */
    private static $obj;

    /**
     * 获取单例
     * @return Request
     */
    public static function init() {
        return !self::$obj ? self::$obj = new Request() : self::$obj;
    }

    /**
     * 初始化
     */
    private function __construct() {
        #清理掉多于引号
        if (function_exists('get_magic_quotes_gpc') && get_magic_quotes_gpc()) {
            $_GET = $this->_strip($_GET);
            $_POST = $this->_strip($_POST);
            $_REQUEST = $this->_strip($_REQUEST);
            $_COOKIE = $this->_strip($_COOKIE);
        }
        #把数据写进自身变量中
        $this->get = (object)$_GET;
        $this->post = (object)$_POST;
        $this->request = (object)$_REQUEST;
        $this->method = $_SERVER['REQUEST_METHOD'];
        $this->host = $_SERVER['SERVER_NAME'];
        $this->scheme = $_SERVER['REQUEST_SCHEME'];
        $this->agent = $_SERVER['HTTP_USER_AGENT'];
        $this->ip = $_SERVER['REMOTE_ADDR'];
    }

    /**
     * 请求方法
     * @var string
     */
    public $method;

    /**
     * 请求主机名
     * @var string
     */
    public $host;

    /**
     * 请求的命名
     * @var string
     */
    public $scheme;

    /**
     * 客户端信息
     * @var string
     */
    public $agent;

    /**
     * uip信息
     * @var string
     */
    public $ip;

    /**
     * 保存FILES信息
     * @var object
     */
    public $files;

    /**
     * 获取Cookie对象
     * @return Cookie
     */
    public function cookie(){
        import('HeXi.Web.Cookie');
        return Cookie::init();
    }

    /**
     * 获取Session对象
     * @return Session
     */
    public function session(){
        import('HeXi.Web.Session');
        return Session::init();
    }

    /**
     * 获取上传对象
     * @param string $formName
     * @return Upload
     */
    public function upload($formName){
        import('HeXi.Web.Upload');
        return new Upload($formName);
    }

    /**
     * 组合Data数据
     * @return array
     */
    public function makeData() {
        $items = func_get_args();
        $data = array();
        foreach ($items as $key => $i) {
            if (!is_int($key)) {
                $data[$key] = $this->request->{$i};
            } else {
                $data[$i] = $this->request->{$i};
            }
        }
        return $data;
    }

    /**
     * 是否GET请求
     * @return bool
     */
    public function isGet() {
        return strtoupper($this->method) == "GET";
    }

    /**
     * 是否POST请求
     * @return bool
     */
    public function isPost() {
        return strtoupper($this->method) == "POST";
    }

    /**
     * 是否Ajax请求
     * @return bool
     */
    public function isAjax() {
        return !strcasecmp($_SERVER['HTTP_X_REQUESTED_WITH'], 'XMLHttpRequest');
    }

    /**
     * 过滤多于引号
     * @param array|string $data
     * @return array|string
     */
    private function _strip($data) {
        return is_array($data) ? array_map(array($this, '_strip'), $data) : stripslashes($data);
    }
}
