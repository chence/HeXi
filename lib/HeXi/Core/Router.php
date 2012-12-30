<?php

/**
 *
 * 路由类
 *
 */
class Router {

    /**
     * 路由类
     * @var Router
     */
    private static $self;

    /**
     * 获取单例
     * @return Router
     */
    public static function instance() {
        return !self::$self ? self::$self = new self() : self::$self;
    }

    /**
     * 默认正则内容
     * @var array
     */
    private $defaultRegex;

    /**
     * 自定义的路由规则
     * @var array
     */
    private $rules;

    /**
     * 自定义规则匹配的值
     * @var array
     */
    private $args;

    /**
     * 私有的初始化方法
     */
    private function __construct() {
        $this->defaultRegex = array(
            'any'    => '(.*)',
            'string' => '([0-9a-zA-Z-_%]+)',
            'int'    => '(\d+)'
        );
        $this->rules        = array();
        $this->args         = array();
    }

    /**
     * 解析路由规则成需要的格式
     * @param string $rule
     * @return array
     */
    private function parseRule($rule) {
        $rule = explode('/', $rule);
        $arg  = array();
        foreach ($rule as $k => $v) {
            #分析需要替换的部分
            if (strstr($v, '::')) {
                $v        = explode('::', $v);
                $arg[]    = $v[0];
                $rule[$k] = $this->defaultRegex[$v[1]];
            }
        }
        return array(
            'regex' => '#^' . join('/', $rule) . '$#',
            'arg'   => $arg
        );
    }

    /**
     * 解析回调规则
     * @param string $call
     * @return array
     */
    private function parseCall($call) {
        if (is_string($call)) {
            return explode('->', $call);
        }
        return $call;
    }

    /**
     * 添加路由规则
     * @param string $rule
     * @param string $call
     * @return Router
     */
    public function add($rule, $call) {
        $rule           = $this->parseRule($rule);
        $rule['call']   = $this->parseCall($call);
        $rule['method'] = 'ALL';
        $this->rules[]  = $rule;
        return $this;
    }

    /**
     * 添加GET路由规则
     * @param string $rule
     * @param string $call
     * @return Router
     */
    public function get($rule, $call) {
        $rule           = $this->parseRule($rule);
        $rule['call']   = $this->parseCall($call);
        $rule['method'] = 'GET';
        $this->rules[]  = $rule;
        return $this;
    }

    /**
     * 添加POST路由规则
     * @param string $rule
     * @param string $call
     * @return Router
     */
    public function post($rule, $call) {
        $rule           = $this->parseRule($rule);
        $rule['call']   = $this->parseCall($call);
        $rule['method'] = 'POST';
        $this->rules[]  = $rule;
        return $this;
    }

    /**
     * 添加PUT路由规则
     * @param string $rule
     * @param string $call
     * @return Router
     */
    public function put($rule, $call) {
        $rule           = $this->parseRule($rule);
        $rule['call']   = $this->parseCall($call);
        $rule['method'] = 'PUT';
        $this->rules[]  = $rule;
        return $this;
    }

    /**
     * 添加DELETE路由规则
     * @param string $rule
     * @param string $call
     * @return Router
     */
    public function delete($rule, $call) {
        $rule           = $this->parseRule($rule);
        $rule['call']   = $this->parseCall($call);
        $rule['method'] = 'DELETE';
        $this->rules[]  = $rule;
        return $this;
    }

    /**
     * 获取并处理当前的URL
     * @return string
     */
    private function parseUrl() {
        $url = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $ext = pathinfo($url, PATHINFO_EXTENSION);
        if ($ext) {
            $url = str_replace('.' . $ext, '', $url);
        }
        return $url;
    }

    /**
     * 匹配自定义规则
     * @param string $url
     * @return bool
     */
    private function matchRule($url) {
        foreach ($this->rules as $rule) {
            if ($rule['method'] !== 'ALL') {
                if ($rule['method'] !== strtoupper($_SERVER['REQUEST_METHOD'])) {
                    continue;
                }
            }
            if (preg_match($rule['regex'], $url, $matches)) {
                array_shift($matches);
                foreach ($matches as $k => $m) {
                    $this->args[] = $m;
                    $name         = $rule['arg'][$k];
                    if ($name) {
                        $_REQUEST[$name] = $m;
                    }
                    return $rule['call'];
                }
            }
        }
        return false;
    }

    /**
     * 获取默认规则
     * @param array $param
     * @return array
     */
    private function autoRule($param) {
        if (!$param) {
            return array('Home', 'index');
        }
        if (!$param[1]) {
            $param[1] = 'index';
        }
        #处理非法的方法名称
        if (!preg_match('#^[a-zA-Z_][0-9a-zA-Z_-]+$#', $param[1])) {
            $param[1] = 'index';
        }
        $param[0] = ucwords($param[0]);
        $param = array_slice($param,0,2);
        return $param;
    }

    /**
     * 路由分发
     * @return bool 返回正确或错误
     */
    public function dispatch() {
        #处理URL和路由参数
        $url              = $this->parseUrl();
        $param            = array_values(array_filter(explode('/', $url)));
        $GLOBALS['param'] = $param;
        #匹配规则获取回调
        $callback         = $this->matchRule($url);
        if (!$callback) {
            $callback = $this->autoRule($param);
        }
        #处理回调操作
        if (is_array($callback)) {
            if (!is_callable($callback)) {
                Error::stop('Invalid Request for Routing', 404);
            }
            $GLOBALS['callback'] = $callback;
            return call_user_func_array(array(HeXi::instance($callback[0]), $callback[1]), $this->args);
        }
        $GLOBALS['callback'] = 'Closure';
        return call_user_func_array($callback, $this->args);
    }
}
