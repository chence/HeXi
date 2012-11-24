<?php
/**
 *
 * HeXi 快速开发框架
 *
 *
 * @copyright Copyright (c) 2012 <hexiaz.com>
 * @author    : FuXiaoHei <fuxiaohei@hexiaz.com>
 * @create: 12-11-10 - 下午9:56
 * @link      : http://hexiaz.com
 *
 *
 */
/**
 *
 * 数据库驱动类的基类
 * 定义了各种不同数据库底层类的统一接口
 * @package Database
 * @author  FuXiaoHei <fuxiaohei@hexiaz.com>
 * @todo 1.完成Mysqli和Sqlite类的数据库驱动基类
 *
 */

abstract class HeXiDriver {
    /**
     * 数据库对象
     * @var PDO
     */
    protected $conn;

    /**
     * 数据库连接的标识名称
     * @var string
     */
    protected $name;

    /**
     * 构造函数
     * @param string $name
     */
    public function __construct($name) {
        $this->name = $name;
        #获取配置信息
        $this->config = Config::get('database.' . $name);
        $this->conn = $this->connect();
        if (!$this->conn) {
            Error::stop('无法连接到数据库 "database.' . $name . '"');
        }
        Event::trigger('appDatabaseConnect:'.$name);
    }

    /**
     * 保存配置信息
     * @var array
     */
    protected $config;

    /**
     * 连接到数据库
     * @return mixed
     */
    abstract public function connect();

    /**
     * 执行SQL，没有查询结果的
     * @param string $statement
     * @param array  $bind
     * @return int|bool
     */
    abstract public function exec($statement, $bind = array());

    /**
     * 查询SQL结果
     * @param string $statement
     * @param array $bind
     * @return object|bool
     */
    abstract public function query($statement, $bind = array());

    /**
     * 查询SQL结果，内容很多
     * @param string $statement
     * @param array $bind
     * @return array
     */
    abstract public function queryAll($statement, $bind = array());

    /**
     * 预备SQL查询
     * @param string $statement
     * @param array $bind
     * @param bool $returnData
     * @return int|object|array|bool
     */
    abstract public function prepare($statement, $bind, $returnData = false);

    /**
     * 最新添加的id
     * @return int|string
     */
    abstract public function lastId();

    /**
     * 操作影响的行数
     * @return int
     */
    abstract public function affectRows();

    /**
     * 处理错误
     */
    abstract public function error();

}
