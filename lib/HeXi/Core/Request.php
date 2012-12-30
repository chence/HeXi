<?php

/**
 *
 * 请求操作类
 *
 * @property string ip
 * @property string host
 * @property string agent
 * @property bool ajax
 * @property bool get
 * @property bool post
 * @property bool put
 * @property bool delete
 *
 */
class Request {

    /**
     * 单例对象
     * @var Request
     */
    private static $self;

    /**
     * 获取单例
     * @return Request
     */
    public static function instance() {
        return !self::$self ? self::$self = new self() : self::$self;
    }

    /**
     * SERVER数据
     * @param string $name
     * @return string|null
     */
    public function server($name) {
        return $name === true ? $_SERVER : $_SERVER[strtoupper($name)];
    }

    /**
     * HTTP头信息
     * @param string $name
     * @return string|null
     */
    public function header($name) {
        $header = strtoupper('http_' . str_replace('-', '_', $name));
        if (function_exists('apache_request_headers')) {
            $headers = apache_request_headers();
            if (!empty($headers[$header])) {
                return $headers[$header];
            }
        }
        return $_SERVER[$header];
    }

    /**
     * 获取ip地址
     * @return string
     */
    public function ip() {
        if ($_SERVER['HTTP_CLIENT_IP']) {
            return $_SERVER['HTTP_CLIENT_IP'];
        }
        if ($_SERVER['HTTP_X_FORWARDED_FOR']) {
            $ip = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            return isset($ip[0]) ? trim($ip[0]) : '';
        }
        return $_SERVER['REMOTE_ADDR'];
    }

    /**
     * 主机名称
     * @return string
     */
    public function host() {
        if ($_SERVER['HTTP_X_FORWARDED_FOR']) {
            $host = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            $host = trim($host[count($host) - 1]);
        } else {
            $host = $_SERVER['HTTP_HOST'];
            if (!$host) {
                $host = $_SERVER['SERVER_NAME'];
            }
        }
        return $host;
    }

    /**
     * 客户端标识
     * @return string
     */
    public function agent() {
        return $this->header('user_agent');
    }

    /**
     * 获取请求方式
     * @param bool|string $method
     * @return bool|mixed
     */
    public function method($method = false) {
        if ($method === false) {
            return $this->server('request_method');
        }
        return strtoupper($this->server('request_method')) == strtoupper($method);
    }

    /**
     * 是否是Ajax请求
     * @return bool
     */
    public function ajax() {
        return 'XMLHttpRequest' == $this->server('Http_X_Requested_With');
    }

    /**
     * 设置一些属性代替方法调用
     * @param string $key
     * @return bool|mixed|null|string
     */
    public function __get($key) {
        switch ($key) {
            case 'ip':
                $this->ip = $this->ip();
                return $this->ip;
            case 'host':
                $this->host = $this->host();
                return $this->host;
            case 'agent':
                $this->agent = $this->agent();
                return $this->agent;
            case 'ajax':
                $this->ajax = $this->ajax();
                return $this->ajax;
            case 'get':
                $this->get = $this->method('get');
                return $this->get;
            case 'post':
                $this->post = $this->method('post');
                return $this->post;
            case 'put':
                $this->put = $this->method('put');
                return $this->put;
            case 'delete':
                $this->delete = $this->method('delete');
                return $this->delete;
        }
        return null;
    }
}
