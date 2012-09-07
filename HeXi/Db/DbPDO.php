<?php
/**
 * PDO数据库类
 */
class DbPDO extends abstractDb {

    /**
     * PDO对象
     * @var PDO
     */
    protected $conn;

    /**
     * PDO数据集对象
     * @var PDOStatement
     */
    protected $stmt;

    /**
     * 初始化方法
     * @throws HeXiException
     */
    public function __construct() {
        #判断PDO是否支持
        if (!class_exists('PDO', false)) {
            error('系统环境不支持PDO数据库');
        }
        parent::__construct();
    }

    /**
     * 连接到数据库
     * @return mixed|void
     */
    public function connect() {
        #生成DSN连接字符串
        $dsn = $this->createDSN();
        #生成连接配置
        $options = $this->createOption();
        try {
            $this->conn = new PDO($dsn, config('database.user'), config('database_password'), $options);
        } catch (PDOException $exc) {
            parent::execError($exc->getMessage());
        }
    }

    /**
     * 生成连接字符串
     * @return string
     */
    private function createDSN() {
        $type = config('database.type');
        switch ($type) {
            case 'mysql':
                $dsn = "mysql:host=" . config('database.host') . ';port=' . config('database.port') . ';dbname=' . config('database.dbname');
                break;
            case 'sqlite':
            default:
                $dsn = "sqlite:" . config('database.file');
        }
        return $dsn;
    }

    /**
     * 生成默认数据库请求
     * @return array
     */
    private function createOption() {
        $type = config('database.type');
        $options = array();
        #默认结果集转化为对象
        $options[PDO::ATTR_DEFAULT_FETCH_MODE] = PDO::FETCH_OBJ;
        #关闭错误，用自有的方法代替
        $options[PDO::ATTR_ERRMODE] = PDO::ERRMODE_SILENT;
        switch ($type) {
            case 'mysql':
                #mysql设置字符串
                $options[PDO::MYSQL_ATTR_INIT_COMMAND] = 'set names "' . config('database.charset') . '"';
                break;
            case 'sqlite':
            default:
                break;
        }
        $options += config('database.options');
        return $options;
    }

    /**
     * 记录SQL
     * @param string $sql
     */
    private function logSql($sql) {
        $this->execSql[] = $sql;
    }

    /**
     * @param string $message 为了规范继承，设置的无用参数
     * 数据库错误
     */
    protected function execError($message = '') {
        $err = $this->conn->errorInfo();
        parent::execError('Code[' . $err[0] . '] ' . $err[2]);
    }

    /**
     * 查询数据库
     * 返回一组结果
     * @param string $statement
     * @return mixed
     */
    public function query($statement) {
        $stmt = $this->conn->query($statement);
        if ($stmt === false) {
            $this->execError();
        }
        $this->logSql($statement);
        return $stmt->fetch();
    }

    /**
     * 查询数据库
     * 返回所有结果
     * @param string $statement
     * @return array
     */
    public function queryAll($statement) {
        $stmt = $this->conn->query($statement);
        if ($stmt === false) {
            $this->execError();
        }
        $this->logSql($statement);
        return $stmt->fetchAll();
    }

    /**
     * 查询数据库
     * 返回结果集对象
     * @param string $statement
     * @return PDOStatement
     */
    public function queryStatement($statement) {
        $stmt = $this->conn->query($statement);
        if ($stmt === false) {
            $this->execError();
        }
        $this->logSql($statement);
        return $stmt;
    }

    /**
     * 执行数据库操作
     * 返回被影响的行数
     * @param string $statement
     * @return int
     */
    public function exec($statement) {
        $res = $this->conn->exec($statement);
        if ($res === false) {
            $this->execError();
        }
        $this->logSql($statement);
        return $res;
    }

    /**
     * 开始事务
     */
    public function beginTrans() {
        $this->conn->beginTransaction();
    }

    /**
     * 执行事务
     */
    public function commit() {
        $this->conn->commit();
    }

    /**
     * 回滚事务
     */
    public function rollback() {
        $this->conn->rollBack();
    }

    /**
     * 获取数值对应的PDO数据类型
     * @param mixed $value
     * @return int|string
     */
    private function getDataType($value) {
        $type = gettype($value);
        switch ($type) {
            case 'integer':
                $type = PDO::PARAM_INT;
                break;
            case 'null':
                $type = PDO::PARAM_NULL;
                break;
            case 'boolean':
                $type = PDO::PARAM_BOOL;
                break;
            case 'string':
            default:
                $type = PDO::PARAM_STR;
                break;
        }
        return $type;
    }

    /**
     * 执行预备语句
     * 返回bool结果或新添加的id
     * @param string $statement
     * @param array $bindData 绑定的数据，key/value格式数组
     * @param bool $isSelect 是否是select操作，如果是，将返回新添加的id
     * @return bool|string
     */
    public function prepareExec($statement, $bindData, $isSelect = false) {
        $this->stmt = $this->conn->prepare($statement);
        if ($this->stmt === false) {
            $this->freeStatement();
            $this->execError();
        }
        $this->logSql($statement);
        foreach ($bindData as $key => $value) {
            #绑定的占位符都是形如:xxx
            $this->stmt->bindValue(':' . $key, $value, $this->getDataType($value));
        }
        $res = $this->stmt->execute();
        if ($res === false) {
            $this->freeStatement();
            $this->execError();
        }
        if ($isSelect) {
            return $this->lastId();
        }
        return $res;
    }

    /**
     * 执行预备语句
     * 利用之前提供的预备语句执行
     * @param array $bindData
     * @param bool $isSelect
     * @return bool|string
     */
    public function stmtExec($bindData, $isSelect = false) {
        #使用之前prepare后生成的PDOStatement
        if (!$this->stmt instanceof PDOStatement) {
            return false;
        }
        #保存一遍SQL语句，说明执行了多次
        $this->logSql($this->stmt->queryString);
        foreach ($bindData as $key => $value) {
            $this->stmt->bindValue(':' . $key, $value, $this->getDataType($value));
        }
        $res = $this->stmt->execute();
        if ($res === false) {
            $this->freeStatement();
            $this->execError();
        }
        if ($isSelect) {
            return $this->lastId();
        }
        return $res;
    }

    /**
     * 查询预备语句
     * 返回一组结果
     * @param string $statement
     * @param array $bindData
     * @return mixed
     */
    public function prepareQuery($statement, $bindData) {
        $this->prepareExec($statement, $bindData);
        return $this->stmt->fetch();
    }

    /**
     * 查询预备语句
     * 返回所有结果
     * @param string $statement
     * @param array $bindData
     * @return array
     */
    public function prepareQueryAll($statement, $bindData) {
        $this->prepareExec($statement, $bindData);
        return $this->stmt->fetchAll();
    }

    /**
     * 查询预备语句
     * 使用上一次查询的结果集对象
     * @param string $bindData
     * @param bool $multi
     * @return array|mixed
     */
    public function stmtQuery($bindData, $multi = false) {
        $this->stmtExec($bindData);
        if ($multi) {
            return $this->stmt->fetchAll();
        }
        return $this->stmt->fetch();
    }

    /**
     * 返回最新添加的id
     * @return bool|string
     */
    public function lastId() {
        if (!$this->conn) {
            return false;
        }
        return $this->conn->lastInsertId();
    }

    /**
     * 返回影响的行数
     * @return bool|int
     */
    public function affectRows() {
        if (!$this->stmt) {
            return false;
        }
        return $this->stmt->rowCount();
    }
}
