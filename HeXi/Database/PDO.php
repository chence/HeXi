<?php
/**
 *
 * HeXi 快速开发框架
 *
 *
 * @copyright Copyright (c) 2012 <hexiaz.com>
 * @author    : FuXiaoHei <fuxiaohei@hexiaz.com>
 * @create: 12-11-28 - 下午10:35
 * @link      : http://hexiaz.com
 *
 *
 */
/**
 * PDO连接的数据库类
 * @package Database
 * @author  FuXiaoHei <fuxiaohei@hexiaz.com>
 *
 */

class Database_PDO extends Database_Base {

    /**
     * PDO对象
     * @var PDO
     */
    protected $database;

    /**
     * PDO查询对象
     * @var PDOStatement
     */
    protected $statement;

    /**
     * 初始化
     * @param string $name 连接名称
     */
    public function __construct($name) {
        $config = Config::get('database.' . $name);
        if(!$config['type']){
            Error::stop('无法获取数据库连接 "'.$name.'" PDO对象的连接数据库类型');
        }
        $type = strtoupper($config['type']);
        if ($type == 'SQLITE') {
            $this->database = $this->_sqlite($config['file']);
        }
        $this->database->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
        $this->database->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);
    }

    /**
     * 获取SQLite连接
     * @param string $file sqlite文件地址
     * @return PDO
     */
    private function _sqlite($file) {
        return new PDO('sqlite:'.$file, null, null);
    }

    /**
     * 执行SQL
     * @param string $sql
     * @param array $param
     * @return bool|int
     */
    public function exec($sql, $param = array()) {
        if (!$param) {
            $res = $this->database->exec($sql);
            if ($res === false) {
                $this->error();
            }
            return $res;
        }
        $this->statement = $this->database->prepare($sql);
        if (!$this->statement) {
            $this->error();
        }
        return $this->statement->execute($param);
    }

    /**
     * 查询SQL
     * @param string $sql
     * @param array $param
     * @return Database_PDO
     */
    public function query($sql, $param = array()) {
        if (!$param) {
            $res = $this->database->query($sql);
            if ($res === false) {
                $this->error();
            }
            return $this;
        }
        $this->statement = $this->database->prepare($sql);
        if (!$this->statement) {
            $this->error();
        }
        $res = $this->statement->execute($param);
        if ($res === false) {
            $this->error();
        }
        return $this;
    }

    /**
     * 获取一组数据
     * @return null|object
     */
    public function fetch() {
        if (!$this->statement) {
            return null;
        }
        return $this->statement->fetch();
    }

    /**
     * 获取所有数据
     * @return array
     */
    public function fetchAll() {
        if (!$this->statement) {
            return array();
        }
        return $this->statement->fetchAll();
    }

    /**
     * 分组获取所有数据
     * @param string $rowName
     * @return array
     */
    public function fetchGroup($rowName) {
        $data = $this->fetchAll();
        $return = array();
        foreach ($data as $value) {
            $return[$value[$rowName]] = $value;
        }
        unset($data);
        return $return;
    }

    /**
     * 最新添加的ID
     * @return string
     */
    public function lastId() {
        return $this->database->lastInsertId();
    }

    /**
     * 影响的行数
     * @return int
     */
    public function affectRows() {
        if (!$this->statement) {
            return 0;
        }
        return $this->statement->rowCount();
    }

    /**
     * 提示错误信息
     */
    protected function error() {
        $error = $this->database->errorInfo();
        parent::error('数据库错误 [' . $error[0] . '] ' . $error[2]);
    }


}
