<?php
/**
 *
 * HeXi 快速开发框架
 *
 *
 * @copyright Copyright (c) 2012 <hexiaz.com>
 * @author    : FuXiaoHei <fuxiaohei@hexiaz.com>
 * @create: 12-11-28 - 下午9:21
 * @link      : http://hexiaz.com
 *
 *
 */
/**
 * 返回请求操作类
 * @package HeXi
 * @author  FuXiaoHei <fuxiaohei@hexiaz.com>
 *
 */

class Response {

    /**
     * 获取一个请求对象
     * @param string $body
     * @param int    $status
     * @param array  $header
     * @return bool|Response
     */
    public static function create($body = '', $status = 200, $header = array()) {
        $response = Register::create('Response');
        $response->body = $body;
        $response->status = $status;
        $response->header = array_merge($response->header, $header);
        return $response;
    }

    /**
     * 状态码
     * @var int
     */
    public $status;

    /**
     * 内容实体
     * @var string
     */
    public $body;

    /**
     * 头信息
     * @var array
     */
    public $header = array();

    /**
     * 初始化
     */
    public function __construct() {
        $this->status = 200;
        $this->body = '';
        $this->set_default();
    }

    /**
     * 设置默认Cache头信息
     */
    private function _default_cache() {
        $this->header['Cache-Control'] = 'no-cache, no-store, max-age=0, must-revalidate';
        $this->header['Expires'] = 'Mon, 26 Jul 1997 05:00:00 GMT';
        $this->header['Pragma'] = 'no-cache';
    }

    /**
     * 设置为默认值
     * @return Response
     */
    public function set_default() {
        $this->header = array(
            'X-Author'     => 'FuXiaoHei',
            'X-Powered-By' => 'HeXi',
            'Content-Type' => 'text/html; charset=utf-8'
        );
        $this->_default_cache();
        return $this;
    }

    /**
     * 设置过期头信息
     * @param int $expire 小于等于0，恢复不过期设置
     * @return Response
     */
    public function cache($expire) {
        if ($expire <= 0) {
            $this->_default_cache();
            return $this;
        }
        $this->header['Last-Modified'] = gmdate('r', time());
        $this->header['Expires'] = gmdate('r', time() + $expire);
        $this->header['Cache-Control'] = 'max-age=' . $expire;
        return $this;
    }

    /**
     * 重定向头信息
     * @param string $url
     * @param bool   $forever 设置永久重定向
     * @return Response
     */
    public function redirect($url, $forever = false) {
        $this->header['Location'] = $url;
        $this->status = $forever === true ? 301 : 302;
        return $this;
    }

    /**
     * 头信息状态码
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
     * @return bool
     */
    public function send() {
        header('HTTP/1.1 ' . $this->status . ' ' . ucwords(self::$statusCode[$this->status]));
        header('Status: ' . $this->status . ' ' . ucwords(self::$statusCode[$this->status]));
        foreach ($this->header as $key => $value) {
            header($key . ': ' . $value);
        }
        if ($this->body) {
            echo $this->body;
            return true;
        }
        return true;
    }
}
