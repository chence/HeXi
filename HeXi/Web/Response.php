<?php
    /**
     * @author FuXiaoHei
     */
class Response {

    private static $obj;

    public static function init() {
        return !self::$obj ? self::$obj = new Response() : self::$obj;
    }

    /**
     * 头信息
     * @var array
     */
    public $header;

    /**
     * 是否已经发送
     * @var bool
     */
    private $isSend;

    /**
     * 私有化构造方法
     */
    private function __construct() {
        #设置一些默认的信息
        $this->contentType = 'text/html';
        $this->charset = "utf-8";
        $this->status = 200;
        $this->body = '';
        $this->download = false;
        $this->header['X-Powered-By'] = 'HeXi 0.5';
        $this->isSend = false;
        $this->pause = false;
        $this->image = false;
        $this->imageDelete = false;
    }

    /**
     * 状态码和对应的文字说明
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
     * 发送缓存头
     * @param int $expire
     */
    public function cached($expire) {
        $this->header['Last-Modified'] = gmdate('r');
        $this->header['Expires:'] = gmdate('r', time() + $expire);
        $this->header['Cache-Control'] = 'max-age=' . $expire;
    }

    /**
     * 重定向
     * @param string $url
     */
    public function redirect($url) {
        $this->header['Location'] = $url;
        $this->status = 302;
    }

    /**
     * 显示图片
     * @param string $type
     * @param string $file
     * @param bool $delete
     */
    public function image($type, $file, $delete = false) {
        if (!is_file($file)) {
            error('要下载的文件 "' . $file . '" 无法找到');
        }
        $this->contentType = $type;
        $this->image = $file;
        $this->imageDelete = (bool)$delete;
    }

    /**
     * 文件下载
     * @param string $file
     * @throws HeXiException
     */
    public function download($file) {
        if (!is_file($file)) {
            error('要下载的文件 "' . $file . '" 无法找到');
        }
        $filename = basename($file);
        $this->contentType = "application/octet-stream";
        $ua = $_SERVER["HTTP_USER_AGENT"];
        $encodedFilename = urlencode($filename);
        $encodedFilename = str_replace("+", "%20", $encodedFilename);
        if (preg_match("/MSIE/", $ua)) {
            $this->header['Content-Disposition'] = 'attachment; filename="' . $encodedFilename . '"';
        } else if (preg_match("/Firefox/", $ua)) {
            $this->header["Content-Disposition"] = "attachment; filename*=\"utf8''" . $filename . '"';
        } else {
            $this->header['Content-Disposition'] = 'attachment; filename="' . $filename . '"';
        }
        $this->header['Content-Disposition'] = 'attachment; filename="' . $filename . '"';
        $this->header["Content-Length"] = filesize($file);
        $this->download = $file;
    }

    /**
     * 视图渲染数据添加到返回内容中
     * @param string $file
     * @param array $data
     */
    public function view($file, $data = array()) {
        $view = View::init();
        $view->viewData += $data;
        $this->body = $view->display($file);
    }

    /**
     * 处理json到返回内容
     * @param array $data
     */
    public function json($data) {
        $this->body = json_encode($data);
    }

    public function xml() {
        /**
         * @todo 返回XML结果
         */
    }

    /**
     * 最终发送
     */
    public function end() {
        #暂停状态，不恢复不发送
        if ($this->pause) {
            return;
        }
        #如果已经发送了，就不发送了。。头信息只发送一次
        if ($this->isSend) {
            return;
        }
        header('HTTP/1.1 ' . $this->status . ' ' . ucwords(self::$httpStatus[$this->status]));
        header('Status: ' . $this->status . ' ' . ucwords(self::$httpStatus[$this->status]));
        header('Content-type:' . $this->contentType . ';charset=' . $this->charset);
        foreach ($this->header as $key => $header) {
            header($key . ':' . $header);
        }
        #显示图片
        if ($this->image) {
            #用image函数输出图片比文件输出慢很多
            $fp = fopen($this->image, 'rb');
            fpassthru($fp);
            fclose($fp);
            #如果是临时图片，删掉
            if ($this->imageDelete) {
                @unlink($this->image);
            }
            return;
        }
        #处理下载文件
        if ($this->download) {
            readfile($this->download);
            return;
        }
        echo $this->body;
        $this->isSend = true;
    }

}
