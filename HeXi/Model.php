<?php
/**
 *
 * HeXi 快速开发框架
 *
 *
 * @copyright Copyright (c) 2012 <hexiaz.com>
 * @author    : FuXiaoHei <fuxiaohei@hexiaz.com>
 * @create: 12-11-28 - 下午6:15
 * @link      : http://hexiaz.com
 *
 *
 */
/**
 * 模型类
 * 模型类的创建和调用
 * @package HeXi
 * @author  FuXiaoHei <fuxiaohei@hexiaz.com>
 *
 */

class Model {

    /**
     * 创建模型类
     * @param string $modelName 模型类名称
     * @return bool|Model_Base|Model_View
     */
    public static function create($modelName) {
        $modelCommand = Config::get('app.model.command') . '.' . str_replace('_', '.', $modelName);
        return Register::create($modelName, $modelCommand);
    }

    /**
     * 调用模型类方法
     * @param string $modelName
     * @param string $method
     * @param array  $args
     * @return mixed
     */
    public static function invoke($modelName, $method, $args = array()) {
        $model = self::create($modelName);
        if (!$model) {
            Error::stop('无法调用模型类 "' . $modelName . '"');
        }
        if (!is_callable(array( $model, $method ))) {
            Error::stop('无法调用模型类 "' . $modelName . '" 的方法 "' . $method . '"');
        }
        return call_user_func_array(array( $model, $method ), $args);
    }
}

/**
 * 模型类基类
 * @package HeXi
 * @author  FuXiaoHei <fuxiaohei@hexiaz.com>
 */
abstract class Model_Base {

    /**
     * 模型类的数据库连接
     * @var bool|Database_Base
     */
    protected $conn;

    /**
     * 初始化方法
     */
    public function __construct() {
        if (Config::get('app.model.database')) {
            $this->conn = Database::connect(Config::get('app.model.database'));
        }
    }

    /**
     * 调用SQL类
     * @param string $table
     * @param string $columns
     * @return Database_Sql
     */
    protected final function sql($table, $columns = '*') {
        return Database_Sql::table($table, $columns);
    }
}

/**
 * 视图模型类基类
 * @package HeXi
 * @author  FuXiaoHei <fuxiaohei@hexiaz.com>
 */
abstract class Model_View extends Model_Base {

    /**
     * 视图对象
     * @var bool|View
     */
    protected $template;

    /**
     * 初始化方法
     * 继承父类的初始化方法
     */
    public function __construct() {
        parent::__construct();
        $this->template = View::create();
    }
}
