<?php

/**
 * 控制器基类
 */
abstract class HeXiController extends HeXiBase {

    /**
     * 初始化Web类
     */
    public function __construct() {
        HeXiWeb::init();
    }

    /**
     * 调用控制器方法
     * @param string $name
     * @return mixed
     */
    public final function run($name) {
        $beforeMethod = '_before_' . $name;
        $afterMethod = '_after_' . $name;
        $controllerName = get_class($this);
        if (method_exists($controllerName, $beforeMethod)) {
            $this->{$beforeMethod}();
        }
        $result = $this->{$name}();
        if (method_exists($controllerName, $afterMethod)) {
            $this->{$afterMethod}();
        }
        return $result;
    }

    /**
     * 析构的时候释放请求
     */
    public function __destruct() {
        HeXiWeb::endResponse();
    }

    /**
     * @param $data
     * @param $type
     * @param $charset
     * @return bool
     */
    protected function _ajax($data, $type, $charset) {
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
            echo $this->_ajaxXml($data);
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
    protected final function _ajaxXml($data) {
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
                    $xml .= '<' . $k . '>' . $this->_ajaxXml($v) . '</' . $k . '>';
                }
            } else {
                #不是关联数组，就是索引数组，使用item替换索引数字
                foreach ($data as $v) {
                    $xml .= '<item>' . $this->_ajaxXml($v) . '</item>';
                }
            }
        } else {
            #直接输出字符串，将字符串中的尖括号转义
            $xml = htmlspecialchars((string)$data);
        }
        return $xml;
    }

    /**
     * 默认方法，没有覆写就抛出错误来
     * @throws HeXiException
     */
    public function index(){
        throw new HeXiException('Undefined default method in "'.get_class($this).'"');
    }
}


/**
 * 控制器异常类
 */
class HeXiControllerException extends HeXiException {

}