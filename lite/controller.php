<?php
/**
 * 控制器类
 * @author fuxiaohei
 * @copyright 12-8-14 fuxiaohei
 *
 */
abstract class controller extends action {

    /**
     * web对象
     * @var web
     */
    protected $web;

    /**
     * 初始化
     */
    public function __construct() {
        $this->web = web::init();
    }

    /**
     * ajax返回
     * @param string $data
     * @param string $type
     * @param string $charset
     * @return bool
     */
    protected function _ajax($data, $type = 'json', $charset = 'utf-8') {
        #如果是json，直接返回结果
        if ($type == 'json') {
            echo json_encode($data);
            return true;
        }
        #如果是xml，发送文档头，同时拼接字符串
        if ($type == 'xml') {
            //$this->sendContentType('text/xml', $charset);
            echo "<?xml version=\"1.0\" encoding=\"" . $charset . "\" ?>";
            echo '<response>';
            echo $this->_ajax_xml($data);
            echo '</response>';
            return true;
        }
        #如果是text，直接输出来
        echo $data;
        return true;
    }

    /**
     * 生成ajax返回的xml字符串
     * @param mixed $data
     * @return string
     */
    protected final function _ajax_xml($data) {
        $xml = '';
        #将对象转化为数组
        if (is_object($data)) {
            $data = (array)$data;
        }
        if (is_array($data)) {
            #判断是不是关联数组
            $isAssoc = (array_keys($data) !== range(0, count($data) - 1));
            if ($isAssoc) {
                foreach ($data as $k=> $v) {
                    $xml .= '<' . $k . '>' . $this->_ajax_xml($v) . '</' . $k . '>';
                }
            } else {
                #不是关联数组，就是索引数组，使用item替换索引数字
                foreach ($data as $v) {
                    $xml .= '<item>' . $this->_ajax_xml($v) . '</item>';
                }
            }
        } else {
            #直接输出字符串，将字符串中的尖括号转义
            $xml = htmlspecialchars((string)$data);
        }
        return $xml;
    }

    /**
     * code
     * @var array
     */
    protected static $code = array(
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
     * 错误http代码
     * @param string $code
     */
    protected function _error($code) {
        header('HTTP/1.1 ' . $code . ' ' . ucwords(self::$code[$code]));
        header('Status: ' . $code . ' ' . ucwords(self::$code[$code]));
    }

    /**
     * get
     * @return bool
     */
    protected function _is_get() {
        return strtoupper($this->web->server('REQUEST_METHOD')) == "GET";
    }

    /**
     * post
     * @return bool
     */
    protected function _is_post() {
        return strtoupper($this->web->server('REQUEST_METHOD')) == "POST";
    }

    /**
     * ajax
     * @return bool
     */
    protected function _is_ajax() {
        return !strcasecmp($this->web->server('HTTP_X_REQUESTED_WITH'), 'XMLHttpRequest');
    }

    /**
     * 默认方法
     * @abstract
     * @return mixed
     */
    abstract public function index();
}
