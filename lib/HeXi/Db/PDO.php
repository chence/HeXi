<?php

/**
 *  PDO类的数据库驱动
 */
class Db_PDO extends Db_Abstract {

    /**
     * PDO对象
     * @var PDO
     */
    protected $pdo;

    /**
     * PDO结果对象
     * @var PDOStatement
     */
    protected $stmt;

    /**
     * 构造方法
     * @param string $name
     */
    public function __construct($name = 'default') {
        if (!extension_loaded('pdo')) {
            Error::stop('不支持PDO数据库连接', 500);
        }
        parent::__construct($name);
        switch (strtolower($this->config['type'])) {
            case 'sqlite':
                $this->pdo = $this->newSqlite();
                break;
            case 'mysql':
                $this->pdo = $this->newMysql();
                break;
            default:
                Error::stop('PDO is not supported your database "' . $this->config['type'] . '"', 500);
                break;
        }
        $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    }

    /**
     * 新建SQLite连接
     * @return PDO
     */
    private function newSqlite() {
        if (!extension_loaded('pdo_sqlite')) {
            Error::stop('PDO is not supported for SQLite', 500);
        }
        if (!$this->config['file']) {
            Error::stop('Database "' . $this->named . '" Configuration is wrong !', 500);
        }
        return new PDO('sqlite:' . $this->config['file']);
    }

    /**
     * 新建MySQL连接
     * @return PDO
     */
    private function newMysql() {
        if (!extension_loaded('pdo_mysql')) {
            Error::stop('PDO is not supported for MySQL', 500);
        }
        if (!$this->config['host'] || !$this->config['username'] || !$this->config['dbname']) {
            Error::stop('Database "' . $this->named . '" Configuration is wrong !', 500);
        }
        $dns = 'mysql:host=' . $this->config['host'] . ';';
        if ($this->config['port']) {
            $dns .= 'port=' . $this->config['port'] . ';';
        }
        $dns .= 'dbname=' . $this->config['dbname'];
        #注意字符集设置
        $option = !$this->config['charset'] ? array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8') :
            array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES ' . $this->config['charset']);
        try {
            $pdo = new PDO($dns, $this->config['username'], $this->config['password'], $option);
        } catch (PDOException $exc) {
            Error::stop($exc->getMessage(), 500);
        }
        return $pdo;
    }

    /**
     * 数据类型和绑定类型的关系
     * @var array
     */
    private $dataType = array(
        'bool'    => PDO::PARAM_BOOL,
        'integer' => PDO::PARAM_INT,
        'float'   => PDO::PARAM_STR,
        'string'  => PDO::PARAM_STR,
        'lob'     => PDO::PARAM_LOB,
        'binary'  => PDO::PARAM_LOB,
        'null'    => PDO::PARAM_NULL,
        'other'   => PDO::PARAM_STR
    );

    /**
     * 获取数据类型
     * @param mixed $value
     * @return mixed
     */
    private function getDataType($value) {
        $type = gettype($value);
        $type = $this->dataType[$type];
        return !$type ? $this->dataType['other'] : $type;
    }


    /**
     * 执行SQL
     * @param string $sql
     * @param array  $arg
     * @return bool|int
     */
    public function exec($sql, $arg = array()) {
        Db_SQL::log($sql);
        if (!$arg) {
            if (!$this->pdo->exec($sql)) {
                $this->error();
            }
        } else {
            $stmt = $this->pdo->prepare($sql);
            if (!$stmt) {
                $this->error();
            }
            #处理绑定参数
            foreach ($arg as $k => $v) {
                $stmt->bindValue(':' . $k, $v, $this->getDataType($v));
            }
            if (!$stmt->execute()) {
                $this->error();
            }
        }
        if (strstr($sql, 'select')) {
            return $this->lastId();
        }
        return true;
    }


    /**
     * 查询SQL
     * @param string $sql
     * @param array  $arg
     * @return bool|array
     */
    public function query($sql, $arg = array()) {
        Db_SQL::log($sql);
        if (!$arg) {
            $this->stmt = $this->pdo->query($sql);
            if (!$this->stmt) {
                $this->error();
            }
            return $this->stmt->fetch();
        }
        $this->stmt = $this->pdo->prepare($sql);
        if (!$this->stmt) {
            $this->error();
        }
        foreach ($arg as $k => $v) {
            $this->stmt->bindValue(':' . $k, $v, $this->getDataType($v));
        }
        if ($this->stmt->execute()) {
            return $this->stmt->fetch();
        }
        return false;
    }

    /**
     * 查询一组SQL
     * @param string $sql
     * @param array  $arg
     * @return array|bool
     */
    public function queryAll($sql, $arg = array()) {
        Db_SQL::log($sql);
        if (!$arg) {
            $this->stmt = $this->pdo->query($sql);
            if (!$this->stmt) {
                $this->error();
            }
            return $this->stmt->fetchAll();
        }
        $this->stmt = $this->pdo->prepare($sql);
        if (!$this->stmt) {
            $this->error();
        }
        foreach ($arg as $k => $v) {
            $this->stmt->bindValue(':' . $k, $v, $this->getDataType($v));
        }
        if ($this->stmt->execute()) {
            return $this->stmt->fetchAll();
        }
        return false;
    }

    /**
     * 抛出错误
     */
    public function error() {
        if (!$this->pdo) {
            Error::stop('Database Unknown Error', 500);
        }
        $error = $this->pdo->errorInfo();
        #释放连接
        $this->pdo  = null;
        $this->stmt = null;
        Error::stop('Database Error - ' . $error[0] . '; ' . $error[2], 500);
    }

    /**
     * 最新添加的id
     * @return int
     */
    public function lastId() {
        if ($this->pdo) {
            return $this->pdo->lastInsertId();
        }
        return 0;
    }

    /**
     * 影响的行数
     * @return int
     */
    public function affectRows() {
        if ($this->stmt) {
            return $this->stmt->rowCount();
        }
        return 0;
    }

}
