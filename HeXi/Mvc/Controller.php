<?php

/**
 * 控制器基类
 * @author FuXiaoHei
 */
class Controller {


    /**
     * 路由对象
     * @var Router
     */
    protected $router;

    /**
     * 初始化
     */
    public function __construct() {
        #初始化时载入请求对象和路由对象
        $this->router = Router::init();
        $this->request = Request::init();
        $this->response = Response::init();
    }

    /**
     * 请求对象
     * @var Request
     */
    protected $request;

    /**
     * 返回请求对象
     * @var Response
     */
    protected $response;

    /**
     * 过滤数组
     * @var array
     */
    protected $filters;

    /**
     * 添加过滤规则
     * @param string $name
     * @param string $command
     * @return Controller
     */
    protected final function filter($name, $command) {
        $this->filters[$name][] = $command;
        return $this;
    }

    /**
     * 执行过滤规则
     * @param string $name
     */
    public function doFilter($name) {
        #如果是before，先执行before_all
        if (strstr($name, 'before_') && $name != 'before_all') {
            $this->doFilter('before_all');
        }
        #然后执行before或after
        if ($this->filters[$name]) {
            foreach ($this->filters[$name] as $filter) {
                $this->{$filter}();
            }
        }
        #如果是after，执行完after再执行after_all
        if (strstr($name, 'after_') && $name != 'after_all') {
            $this->doFilter('after_all');
        }
    }

    /**
     * 调用Model方法
     * @param string $command 操作如xxx->yyy即xxxModel的yyy方法
     * @return mixed
     */
    protected function model($command) {
        $args = func_get_args();
        $command = explode('->', $command);
        $model = Model::get($command[0]);
        if (!is_callable(array($model, $command[1]))) {
            HeXi::error('无法在模型 "' . get_class($model) . '" 调用方法 "' . $command[1] . '"');
        }
        array_shift($args);
        return call_user_func_array(array($model, $command[1]), $args);
    }

    /**
     * 析构方法
     */
    public function __destruct() {
        #析构是释放请求
        $this->response->end();
    }
}
