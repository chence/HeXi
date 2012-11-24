<?php
/**
 *
 * HeXi 快速开发框架
 *
 *
 * @copyright Copyright (c) 2012 <hexiaz.com>
 * @author    : FuXiaoHei <fuxiaohei@hexiaz.com>
 * @create: 12-11-8 - 下午12:17
 * @link      : http://hexiaz.com
 *
 *
 */
/**
 * PDO数据驱动类的封装
 * 按照抽象的方法并实现
 * @package Database
 * @author  FuXiaoHei <fuxiaohei@hexiaz.com>
 *
 */

class PDODriver extends HeXiDriver {

    /**
     * 数据库连接对象，PDO对象
     * @var PDO
     */
    protected $conn;

    /**
     * 影响结果的行数
     * @var int
     */
    private $affectRows = 0;

    /**
     * 连接到数据库
     * 生成PDO对象
     * @return bool|PDO
     */
    public function connect() {
        $type = $this->config['type'];
        switch (strtolower($type)) {
            case 'sqlite':
                return $this->getSqlite();
        }
        return false;
    }

    /**
     * 获取Sqlite的PDO对象
     * @return PDO
     */
    private function getSqlite() {
        $file = Register::cmd($this->config['cmd']).'.db';
        $dsn = 'sqlite:' . $file;
        $options = array(
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_SILENT
        );
        #使用try...catch捕获连接错误
        try {
            $pdo = new PDO($dsn, null, null, $options);
        } catch (PDOException $exc) {
            Error::stop('数据库 "database.' . $this->name . '" 连接错误：' . $exc->getMessage());
        }
        return $pdo;
    }

    /**
     * 执行SQL操作
     * @param string $statement
     * @param array  $bind
     * @return bool|int
     */
    public function exec($statement, $bind = array()) {
        #处理绑定参数的方式
        if ($bind) {
            $stmt = $this->conn->prepare($statement);
            if (!$stmt) {
                $this->error();
            }
            $result = $stmt->execute($bind);
            if ($result === false) {
                $this->error();
            }
            #添加影响的函数，以便随时返回结果
            $this->affectRows = $stmt->rowCount();
            return $result;
        }
        $result = $this->conn->exec($statement);
        if (strstr($result, 'update')) {
            $this->affectRows = $result;
        }
        return $result;
    }

    /**
     * 查询SQL操作
     * @param string $statement
     * @param array  $bind
     * @return mixed
     */
    public function query($statement, $bind = array()) {
        if ($bind) {
            $stmt = $this->conn->prepare($statement);
            if (!$stmt) {
                $this->error();
            }
            $result = $stmt->execute($bind);
            if ($result === false) {
                $this->error();
            }
            $this->affectRows = $stmt->rowCount();
            return $stmt->fetch();
        }
        $stmt = $this->conn->query($statement);
        if ($stmt === false) {
            $this->error();
        }
        return $stmt->fetch();
    }

    /**
     * 查询SQL操作，返回一组结果
     * @param string $statement
     * @param array  $bind
     * @return array
     */
    public function queryAll($statement, $bind = array()) {
        if ($bind) {
            $stmt = $this->conn->prepare($statement);
            if (!$stmt) {
                $this->error();
            }
            $result = $stmt->execute($bind);
            if ($result === false) {
                $this->error();
            }
            $this->affectRows = $stmt->rowCount();
            return $stmt->fetchAll();
        }
        $stmt = $this->conn->query($statement);
        if ($stmt === false) {
            $this->error();
        }
        return $stmt->fetchAll();
    }

    /**
     * 预备SQL语句，并返回结果
     * @param string $statement
     * @param array  $bind
     * @param bool   $returnData
     * @return array|int|mixed|string
     */
    public function prepare($statement, $bind, $returnData = false) {
        $stmt = $this->conn->prepare($statement);
        if (!$stmt) {
            $this->error();
        }
        $result = $stmt->execute($bind);
        if ($result === false) {
            $this->error();
        }
        $this->affectRows = $stmt->rowCount();
        #如果需要返回数据的情况
        if ($returnData) {
            return $returnData == 'all' ? $stmt->fetchAll() : $stmt->fetch();
        }
        #添加数据时，返回最新的id
        if (strstr($statement, 'insert')) {
            return $this->lastId();
        }
        #不返回数据，返回影响的行数
        return $this->affectRows();
    }

    /**
     * 最新添加的id
     * @return string
     */
    public function lastId() {
        return $this->conn->lastInsertId();
    }

    /**
     * 影响的行数
     * @return int
     */
    public function affectRows() {
        return $this->affectRows;
    }

    /**
     * 处理错误信息
     */
    public function error() {
        $error = $this->conn->errorInfo();
        Error::stop('数据库 "database.' . $this->name . '" 执行错误：' . $this->conn->errorCode() . ' , ' . $error[2]);
    }

}
