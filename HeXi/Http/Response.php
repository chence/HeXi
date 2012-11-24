<?php
/**
 *
 * HeXi 快速开发框架
 *
 *
 * @copyright Copyright (c) 2012 <hexiaz.com>
 * @author    : FuXiaoHei <fuxiaohei@hexiaz.com>
 * @create: 12-11-23 - 下午7:47
 * @link      : http://hexiaz.com
 *
 *
 */
/**
 *
 * 返回请求的操作类
 * @package Http
 * @author  FuXiaoHei <fuxiaohei@hexiaz.com>
 *
 */

class Response {

    /**
     * 返回请求的对象
     * @var bool
     */
    private static $instance = false;

    /**
     * 生成一个返回请求的对象
     * 只生成一次
     * @return Response
     */
    public static function create() {
        if (!self::$instance) {
            self::$instance = new Response();
        }
        return self::$instance;
    }

    /**
     * 生成一个新的返回请求
     * @return Response
     */
    public static function reset() {
        self::$instance = new Response();
        return self::$instance;
    }

    /**
     * 状态码
     * @var int
     */
    private $status;

    /**
     * 文档类型
     * @var string
     */
    private $contentType;

    /**
     * 文档编码
     * @var string
     */
    private $charset;

    /**
     * 头信息
     * @var array
     */
    private $header = array();

    /**
     * 内容体
     * 或者是文件地址
     * @var string
     */
    private $body;

    /**
     * 是否是文件下载
     * @var bool
     */
    private $file;

    /**
     * 是否已经发送了
     * @var bool
     */
    private $isSend;

    /**
     * 私有化方法
     * 生成对象
     */
    private function __construct() {
        $this->status = 200;
        $this->contentType = 'text/html';
        $this->charset = 'utf-8';
        $this->body = '';
        $this->file = false;
        $this->isSend = false;
        $this->header = array(
            'X-Author'     => 'FuXiaoHei',
            'X-Powered-By' => 'HeXi Preview'
        );
    }

    /**
     * 获取状态码
     * 或者设置状态码
     * @param bool|int $status
     * @return int|Response
     */
    public function status($status = false) {
        if (!$status) {
            return $this->status;
        }
        $this->status = $status;
        return $this;
    }

    /**
     * 设置文档类型
     * 或者添加文档类型
     * @param bool|string $contentType
     * @return Response|string
     */
    public function contentType($contentType = false) {
        if (!$contentType) {
            return $this->contentType;
        }
        $this->contentType = $contentType;
        return $this;
    }

    /**
     * 设置编码
     * 获取编码
     * @param bool|string $charset
     * @return Response|string
     */
    public function charset($charset = false) {
        if (!$charset) {
            return $this->charset;
        }
        $this->charset = $charset;
        return $this;
    }

    /**
     * 设置内容
     * 获取内容
     * @param bool|string $body
     * @return Response|string
     */
    public function body($body = false) {
        if (!$body) {
            return $this->body;
        }
        $this->body = $body;
        return $this;
    }

    /**
     * 添加头信息
     * @param string|array $key
     * @param string       $value
     * @return Response
     */
    public function header($key, $value = null) {
        if (is_array($key)) {
            $this->header = array_merge($this->header, $key);
            return $this;
        }
        $this->header[$key] = $value;
        return $this;
    }

    /**
     * 添加缓存信息
     * @param int $expire
     * @return Response
     */
    public function cache($expire) {
        $this->header('Last-Modified', gmdate('r', NOW));
        $this->header('Expires', gmdate('r', NOW + $expire));
        $this->header('Cache-Control', 'max-age=' . $expire);
        return $this;
    }

    /**
     * 重定向
     * @param string $url
     * @return Response
     */
    public function redirect($url) {
        $this->status = 302;
        $this->header('Location', $url);
        return $this;
    }

    /**
     * 下载文件
     * @param string $file
     * @return Response
     */
    public function download($file) {
        if (!is_file($file)) {
            Error::stop('无法找到提供下载的文件 "' . $file . '"');
        }
        $fileName = basename($file);
        $ua = $_SERVER["HTTP_USER_AGENT"];
        $encoded_filename = rawurlencode($fileName);
        $headers = array();
        if (preg_match("/MSIE/", $ua)) {
            $headers['Content-Disposition'] = 'attachment; filename="' . $encoded_filename . '"';
        } else if (preg_match("/Firefox/", $ua)) {
            $headers['Content-Disposition'] = "attachment; filename*=\"utf8''" . $fileName . '"';
        } else {
            $headers['Content-Disposition'] = 'attachment; filename="' . $fileName . '"';
        }
        $headers['Content-Length'] = filesize($file);
        $this->header = array_merge($this->header, $headers);
        $this->body = $file;
        $this->file = true;
        return $this;
    }

    /**
     * 状态码
     * @var array
     */
    private static $statusCode = array(
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
     * 发送头信息
     */
    private function sendHeader() {
        header('HTTP/1.1 ' . $this->status . ' ' . ucwords(self::$statusCode[$this->status]));
        header('Status: ' . $this->status . ' ' . ucwords(self::$statusCode[$this->status]));
        $withCharset = array(
            'text/html',
            'text/xml',
            'text/javascript',
            'application/javascript',
        );
        if (in_array($this->contentType, $withCharset)) {
            $this->header['Content-Type'] = $this->contentType . ';charset=' . $this->charset;
        } else {
            $this->header['Content-Type'] = $this->contentType;
        }
        foreach ($this->header as $key => $value) {
            header($key . ': ' . $value);
        }
    }

    /**
     * 发送内容体
     */
    private function sendBody() {
        if ($this->file) {
            readfile($this->body);
            return;
        }
        echo $this->body;
    }

    /**
     * 最后发送出去
     */
    public function send() {
        if ($this->isSend) {
            return;
        }
        $this->sendHeader();
        $this->isSend = true;
        $this->sendBody();
    }


}
