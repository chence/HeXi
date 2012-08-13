<?php


abstract class HeXiDriver {

    /**
     * 数据库连接对象
     * @var object
     */
    protected $conn;

    /**
     * 数据集对象
     * @var object
     */
    protected $stmt;

    /**
     * 驱动对象的名称
     * @var string
     */
    protected $name;

    /**
     * 获取数据库配置
     * @var array
     */
    protected $config;

    /**
     * 初始化
     * @param string $name
     */
    public function __construct($name = 'default') {
        $this->name = $name;
        $this->config = $GLOBALS['config']['database_' . $name];
        $this->connect($this->config);
        $this->execSql = array();
    }

    /**
     * 获取驱动对象的名称
     * @return string
     */
    public function getDriverObjectName() {
        return $this->name;
    }

    /**
     * 连接到数据库
     * @abstract
     * @param array $config
     * @return mixed
     */
    abstract public function connect($config);

    /**
     * 断开连接
     */
    public function disConnect() {
        #释放数据集
        $this->freeStatement();
        $this->conn = null;
    }

    /**
     * 获取连接对象，实际上就是PDO等底层对象
     * @return object
     */
    public function getConnection() {
        return $this->conn;
    }

    /**
     * 获取数据集对象
     * @return object
     */
    public function getStatement() {
        return $this->stmt;
    }

    /**
     * 释放数据集对象
     */
    public function freeStatement() {
        $this->stmt = null;
    }

    /**
     * 查询数据库
     * @abstract
     * @param string $statement
     * @return mixed
     */
    abstract public function query($statement);

    /**
     * 查询许多数据
     * @abstract
     * @param string $statement
     * @return mixed
     */
    abstract public function queryAll($statement);

    /**
     * 查询后返回数据集对象，如PDOStatement
     * @abstract
     * @param string $statement
     * @return mixed
     */
    abstract public function queryStatement($statement);

    /**
     * 执行数据库操作
     * @abstract
     * @param string $statement
     * @return mixed
     */
    abstract public function exec($statement);

    /**
     * 开启事务
     * @abstract
     * @return mixed
     */
    abstract public function beginTrans();

    /**
     * 执行事务
     * @abstract
     * @return mixed
     */
    abstract public function commit();

    /**
     * 回滚事务
     * @abstract
     * @return mixed
     */
    abstract public function rollback();

    /**
     * 执行预备语句
     * @abstract
     * @param string $statement
     * @param array $bindData 绑定的数据，必须是key/value格式的，用以绑定
     * @param bool $isSelect 如果true，就返回添加的id
     * @return mixed
     */
    abstract public function prepareExec($statement, $bindData, $isSelect = false);

    /**
     * 使用已经用好的预备语句
     * @abstract
     * @param array $bindData
     * @param bool $isSelect
     * @return mixed
     */
    abstract public function stmtExec($bindData, $isSelect = false);

    /**
     * 执行查询的预备语句
     * @abstract
     * @param string $statement
     * @param array $bindData
     * @return mixed
     */
    abstract public function prepareQuery($statement, $bindData);

    /**
     * 执行查询预备语句，返回许多结果的
     * @abstract
     * @param string $statement
     * @param array $bindData
     * @return mixed
     */
    abstract public function prepareQueryAll($statement, $bindData);

    /**
     * 使用以前的预备语句查询数据
     * @abstract
     * @param array $bindData
     * @param bool $multi
     * @return mixed
     */
    abstract public function stmtQuery($bindData, $multi = false);

    /**
     * 最后添加的id
     * @abstract
     * @return mixed
     */
    abstract public function lastId();

    /**
     * 影响的函数
     * @abstract
     * @return mixed
     */
    abstract public function affectRows();

    /**
     * 抛出数据库错误
     * @param string $message
     * @throws HeXiDriverException
     */
    protected function execError($message = '') {
        throw new HeXiDriverException($message);
    }

    /**
     * 保存执行的SQL
     * @var array
     */
    protected $execSql;

    /**
     * 返回执行的SQL
     * @return array
     */
    public function getExecSql() {
        return $this->execSql;
    }

}

class HeXiDriverException extends HeXiException {

}