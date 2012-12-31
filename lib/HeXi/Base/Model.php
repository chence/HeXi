<?php
/**
 *
 * 模型类的基类
 *
 * 带有简单的单表操作
 *
 */
abstract class Base_Model extends Base_Class {

    /**
     * 数据库连接
     * @var Db_Abstract|Db_PDO
     */
    protected $conn;

    /**
     * 单表名称
     * @var string
     */
    protected $table;

    /**
     * 构造方法
     */
    public function __construct() {
        parent::__construct();
        $this->connect();
    }

    /**
     * 抽象连接
     * 设置连接到数据库
     * 表格名称
     */
    abstract protected function connect();

    /**
     * 获取一个SQL生成对象
     * @param string $table
     * @param string $column
     * @return Db_SQL
     */
    protected function sql($table, $column = '*') {
        return Db_SQL::table($table, $column);
    }

    /**
     * 检查单表操作的数据库连接和表名称
     */
    protected final function checkTable() {
        if (!$this->conn || !$this->table) {
            Error::stop('Model can not operate Database without connection or table !', 500);
        }
    }

    /**
     * 保存数据
     * 支持添加和替换
     * 只支持WHERE语句
     * @param array       $data
     * @param null|string $keyColumn
     * @return bool|int
     */
    protected function save($data, $keyColumn = null) {
        $this->checkTable();
        $useData = $data;
        #处理实际数据和条件数据
        if ($keyColumn) {
            $keyColumn = array_flip(explode(',', $keyColumn));
            $useData   = array_diff_key($data, $keyColumn);
        }
        $keyData = array_diff($data, $useData);
        #开始组装SQL
        $sql = $this->sql($this->table, join(',', array_keys($useData)));
        #更新实际数据值为需要的名称
        foreach ($useData as $k => $v) {
            $useData['column_' . $k] = $v;
            unset($useData[$k]);
        }
        if ($keyData) {
            foreach ($keyData as $k => $v) {
                $sql->where($k . ' = :' . $k);
            }
            #当作update处理
            return $this->conn->exec($sql->update(), array_merge($useData, $keyData));
        }
        #当作insert处理
        return $this->conn->exec($sql->insert(), $useData);
    }

    /**
     * 删除数据
     * 只支持WHERE和LIMIT条件
     * @param array $condition
     * @param array $args
     * @return bool|int
     */
    protected function remove($condition, $args = array()) {
        $this->checkTable();
        $sql = $this->sql($this->table);
        if ($condition['where']) {
            foreach ($condition['where'] as $c) {
                $sql->where($c);
            }
        }
        if ($condition['limit']) {
            $sql->limit((int)$condition['limit']);
        }
        return $this->conn->exec($sql->delete(), $args);
    }

    /**
     * 查询操作
     * 支持WHERE, ORDER和LIMIT条件
     * @param array $condition
     * @param array $args
     * @return array|bool|null|object
     */
    protected function find($condition, $args = array()) {
        $this->checkTable();
        #处理字段
        $sql = isset($condition['column']) ? $this->sql($this->table, $condition['column']) : $this->sql($this->table);
        #处理WHERE
        if ($condition['where']) {
            if (is_array($condition['where'])) {
                foreach ($condition['where'] as $c) {
                    $sql->where($c);
                }
            } else {
                $sql->where($condition['where']);
            }
        }
        #处理limit
        if ($condition['limit']) {
            $sql->limit((int)$condition['limit']);
        } elseif ($condition['pager']) {
            $sql->pager($condition['pager'][0], $condition['pager'][1]);
        }
        if ($condition['order']) {
            $sql->order($condition['order']);
        }
        #多结果还是单一结果
        if ($condition['all']) {
            return $this->conn->queryAll($sql->select(), $args);
        }
        return $this->conn->query($sql->select(), $args);
    }

    /**
     * 转换数据给SQL类生成的SQL
     * @param array $data
     * @param string $columns
     * @return array
     */
    protected function buildData(array &$data, $columns) {
        $columns = explode(',', $columns);
        foreach ($data as $k => $v) {
            if (in_array($k, $columns)) {
                $data['column_' . $k] = $v;
                unset($data[$k]);
            }
        }
        return $data;
    }

}
