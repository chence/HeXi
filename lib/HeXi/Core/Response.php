<?php
/**
 *
 * 处理返回类
 *
 */
class Response {

    /**
     * 状态码说明文字
     * @var array
     */
    protected static $statusTexts = array(
        '100' => 'Continue',
        '101' => 'Switching Protocols',
        '200' => 'OK',
        '201' => 'Created',
        '202' => 'Accepted',
        '203' => 'Non-Authoritative Information',
        '204' => 'No Content',
        '205' => 'Reset Content',
        '206' => 'Partial Content',
        '300' => 'Multiple Choices',
        '301' => 'Moved Permanently',
        '302' => 'Found',
        '303' => 'See Other',
        '304' => 'Not Modified',
        '305' => 'Use Proxy',
        '306' => '(Unused)',
        '307' => 'Temporary Redirect',
        '400' => 'Bad Request',
        '401' => 'Unauthorized',
        '402' => 'Payment Required',
        '403' => 'Forbidden',
        '404' => 'Not Found',
        '405' => 'Method Not Allowed',
        '406' => 'Not Acceptable',
        '407' => 'Proxy Authentication Required',
        '408' => 'Request Timeout',
        '409' => 'Conflict',
        '410' => 'Gone',
        '411' => 'Length Required',
        '412' => 'Precondition Failed',
        '413' => 'Request Entity Too Large',
        '414' => 'Request-URI Too Long',
        '415' => 'Unsupported Media Type',
        '416' => 'Requested Range Not Satisfiable',
        '417' => 'Expectation Failed',
        '500' => 'Internal Server Error',
        '501' => 'Not Implemented',
        '502' => 'Bad Gateway',
        '503' => 'Service Unavailable',
        '504' => 'Gateway Timeout',
        '505' => 'HTTP Version Not Supported',
    );

    /**
     * 单例对象
     * @var Response
     */
    private static $self;

    /**
     * 获取单例
     * @return Response
     */
    public static function instance() {
        return !self::$self ? self::$self = new self() : self::$self;
    }

    /**
     * 状态码
     * @var int
     */
    public $status;

    /**
     * 文档类型
     * @var string
     */
    public $contentType;

    /**
     * 文本内容
     * @var string
     */
    public $charset;

    /**
     * 头信息
     * @var array
     */
    public $header = array();

    /**
     * 内容文本
     * @var string
     */
    public $content;

    /**
     * 是否已经发送
     * @var bool
     */
    public $isSend;

    /**
     * 私有的初始化
     */
    private function __construct() {
        #默认返回内容
        $this->status      = 200;
        $this->contentType = 'text/html';
        $this->charset     = 'UTF-8';
        $this->content     = '';
        #默认头信息
        $this->header = array(
            'X-Powered-By' => 'HeXi 2.0 alpha',
            'X-Author'     => 'FuXiaoHei'
        );
        $this->setNoCache();
        $this->isSend = false;
    }

    /**
     * 组合头信息
     * @param array $options
     * @return Response
     */
    public function build($options) {
        if (isset($options['status'])) {
            $this->status = $options['status'];
        }
        if (isset($options['contentType'])) {
            $this->contentType = $options['contentType'];
        }
        if (isset($options['charset'])) {
            $this->charset = $options['charset'];
        }
        if (isset($options['cache'])) {
            $this->cache($options['cache']);
        }
        if (isset($options['header'])) {
            $this->header = $options['header'] + $this->header;
        }
        return $this;
    }

    /**
     * 设置返回内容文本
     * @param string $cnt
     * @return Response
     */
    public function content($cnt) {
        $this->content = $cnt;
        return $this;
    }

    /**
     * 设置无缓存
     */
    private function setNoCache() {
        $this->header['Cache-Control'] = 'no-cache, no-store, max-age=0, must-revalidate';
        $this->header['Expires']       = 'Mon, 26 Jul 1997 05:00:00 GMT';
        $this->header['Pragma']        = 'no-cache';
    }

    /**
     * 设置缓存
     * @param int $expire 小于1时设置无缓存
     * @return Response
     */
    public function cache($expire = 0) {
        if ($expire < 1) {
            $this->setNoCache();
        } else {
            $this->header['Last-Modified'] = gmdate('r', time());
            $this->header['Expires']       = gmdate('r', time() + $expire);
            $this->header['Cache-Control'] = 'max-age=' . $expire;
        }
        return $this;
    }

    /**
     * 添加头信息
     * @param string      $key
     * @param null|string $value 如果false或null，删除头信息
     * @return Response
     */
    public function header($key, $value = null) {
        if ($value === false || $value === null) {
            unset($this->header[$key]);
            return $this;
        }
        $this->header[$key] = $value;
        return $this;
    }

    /**
     * 跳转地址
     * @param string $url
     * @param bool   $forever
     * @return Response
     */
    public function redirect($url, $forever = false) {
        $this->header['Location'] = $url;
        $this->status             = $forever === true ? 301 : 302;
        return $this;
    }

    /**
     * 发送返回请求
     * @return bool
     */
    public function send() {
        if ($this->isSend) {
            return true;
        }
        #状态信息
        header('HTTP/1.1 ' . $this->status . ' ' . ucwords(self::$statusTexts[$this->status]));
        header('Status: ' . $this->status . ' ' . ucwords(self::$statusTexts[$this->status]));
        #文档信息
        if (strstr($this->contentType, 'text/')) {
            header('Content-Type:' . $this->contentType . ';charset=' . $this->charset);
        } else {
            header('Content-Type:' . $this->contentType);
        }
        #头信息
        foreach ($this->header as $key => $value) {
            header($key . ': ' . $value);
        }
        $this->isSend = true;
        #输出内容
        if ($this->content) {
            echo $this->content;
            return true;
        }
        return true;
    }
}
