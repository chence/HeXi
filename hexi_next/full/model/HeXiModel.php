<?php


/**
 * 模块类
 */
class HeXiModel extends HeXiBase {

    /**
     * 表名称
     * @var string
     */
    protected $table;

    /**
     * 数据库连接
     * @var HeXiDriver|HeXiPdoDriver
     */
    protected $conn;

    /**
     * 主键名称，用于计数
     * @var string
     */
    protected $key;

    /**
     * sql参数
     * @var array
     */
    protected $args = array();

    /**
     * 初始化
     */
    public function __construct() {
        $this->key = 'id';
    }

    /**
     * 设置表名
     * @param string $table
     * @return HeXiModel
     */
    protected function _table($table) {
        $this->args['table'] = $table;
        return $this;
    }

    /**
     * 设置字段
     * @param string $fields 逗号隔开
     * @return HeXiModel
     */
    protected function _field($fields) {
        $this->args['fields'] = $fields;
        return $this;
    }

    /**
     * 设置条件
     * @param string $condition
     * @return HeXiModel
     */
    protected function _where($condition) {
        $this->args['where'][] = $condition;
        return $this;
    }

    /**
     * 设置排序条件
     * @param string $order
     * @return HeXiModel
     */
    protected function _order($order) {
        $this->args['order'] = $order;
        return $this;
    }

    /**
     * 设置数量偏移
     * @param int $offset
     * @return HeXiModel
     */
    protected function _limit($offset) {
        $this->args['limit'] = $offset;
        return $this;
    }

    /**
     * 设置分页
     * @param int $page
     * @param int $size
     * @return HeXiModel
     */
    protected function _page($page, $size) {
        $this->args['limit'] = ($page - 1) * $size . ',' . $size;
        return $this;
    }

    /**
     * 设置group条件
     * @param string $group
     * @param bool|string $having
     * @return HeXiModel
     */
    protected function _group($group, $having = false) {
        $this->args['group'] = $group;
        if ($having) {
            $this->args['having'] = $having;
        }
        return $this;
    }

    /**
     * 设置join条件
     * @param string $table
     * @param string $on
     * @return HeXiModel
     */
    protected function _join($table, $on) {
        $this->args['join'] = $table;
        $this->args['on'] = $on;
        return $this;
    }

    /**
     * 设置联合查询
     * @param string $statement
     * @return HeXiModel
     */
    protected function _union($statement) {
        $this->args['union'][] = $statement;
        return $this;
    }

    /**
     * 设置唯一列查询
     * @param string $field
     * @return HeXiModel
     */
    protected function _distinct($field) {
        $this->args['distinct'] = $field;
        return $this;
    }

    /**
     * 设置主键
     * @param string $key
     * @return HeXiModel
     */
    protected function _key($key) {
        $this->key = $key;
        return $this;
    }

    /**
     * 设置数据映射
     * @param array $mapArray
     * @param bool $filter 是否过滤值
     * @return HeXiModel
     */
    protected function _mapData($mapArray, $filter = false) {
        if ($filter) {
            foreach ($mapArray as $name=> $name2) {
                $name2 = explode(':', $name2);
                $this->args['data'][$name] = $this->_filterData(HeXiWeb::Request($name2[1]), $name[0]);
            }
            return $this;
        }
        foreach ($mapArray as $name=> $name2) {
            $this->args['data'][$name] = HeXiWeb::Request($name2);
        }
        return $this;
    }

    /**
     * 过滤值
     * @param mixed $value
     * @param string $type
     * @return bool|int|string
     */
    protected function _filterData($value, $type) {
        if ($type == 'int') {
            return (int)$value;
        }
        if ($type == 'number') {
            return $value + 0;
        }
        if ($type == 'bool') {
            return (boolean)$value;
        }
        return (string)$value;
    }

    //----------------------

    /**
     * 解析字段
     * @return string
     */
    protected function _parseFields() {
        if ($this->args['distinct']) {
            return 'DISTINCT ' . $this->args['distinct'];
        }
        return $this->args['fields'];
    }

    /**
     * 解析where条件
     * @return string
     */
    protected function _parseWhere() {
        return isset($this->args['where']) ? ' WHERE ' . join(' AND ', $this->args['where']) : '';
    }

    /**
     * 解析排序条件
     * @return string
     */
    protected function _parseOrder() {
        return isset($this->args['order']) ? ' ORDER BY ' . $this->args['order'] : '';
    }

    /**
     * 解析数量限制
     * @return string
     */
    protected function _parseLimit() {
        return isset($this->args['limit']) ? ' LIMIT ' . $this->args['limit'] : '';
    }

    /**
     * 解析group
     * @return string
     */
    protected function _parseGroup() {
        if (!$this->args['group']) {
            return '';
        }
        $string = ' GROUP BY ' . $this->args['group'];
        if ($this->args['having']) {
            $string .= ' HAVING ' . $this->args['having'];
        }
        return $string;
    }

    /**
     * 解析join
     * @return string
     */
    protected function _parseJoin() {
        return isset($this->args['join']) ? ' JOIN ' . $this->args['join'] . ' ON ' . $this->args['on'] : '';
    }

    /**
     * 解析联合查询
     * @return string
     */
    protected function _parseUnion() {
        return isset($this->args['union']) ? ' UNION ' . join(' AND ', $this->args['union']) : '';
    }

    /**
     * 凭借SQL
     * 注意insert和update是预备语句
     * @param string $type 默认select语句
     * @return string
     */
    protected function _buildSql($type = "select") {
        if ($type == "delete") {
            $sql = 'DELETE FROM ' . $this->args['table'] . $this->_parseWhere();
            return $sql;
        }
        if ($type == 'insert') {
            $sql = "INSERT INTO " . $this->args['table'] . '(' . $this->args['fields'] . ') VALUES (';
            $sql .= join(',', array_fill(0, count(explode(',', $this->args['fields'])), '?')) . ')';
            return $sql;
        }
        if ($type == 'update') {
            $sql = "UPDATE " . $this->args['table'] . ' SET ' . join(' = ? ,', explode(',', $this->args['fields'])) . ' = ?';
            $sql .= $this->_parseWhere();
            return $sql;
        }
        $sql = 'SELECT ' . $this->_parseFields() . ' FROM ' . $this->args['table'];
        $sql .= $this->_parseJoin();
        $sql .= $this->_parseGroup();
        $sql .= $this->_parseWhere();
        $sql .= $this->_parseOrder();
        $sql .= $this->_parseLimit();
        $sql .= $this->_parseUnion();
        return $sql;
    }

    //----------------------

    /**
     * 查询数据
     * @return array|mixed
     */
    protected function _select() {
        $sql = $this->_buildSql();
        $result = $this->conn->queryAll($sql);
        if (count($result) == 1) {
            return $result[0];
        }
        return $result;
    }

    /**
     * 更新数据
     * @return bool|mixed|string
     */
    protected function _update() {
        $sql = $this->_buildSql('update');
        return $this->conn->prepareExec($sql, $this->args['data']);
    }

    /**
     * 添加数据
     * @return bool|mixed|string
     */
    protected function _insert() {
        $sql = $this->_buildSql('insert');
        return $this->conn->prepareExec($sql, $this->args['data']);
    }

    /**
     * 删除数据
     * @return int|mixed
     */
    protected function _delete() {
        $sql = $this->_buildSql('delete');
        return $this->conn->exec($sql);
    }

    /**
     * 计数
     * @return bool
     */
    protected function _count() {
        $sql = 'SELECT count(' . $this->key . ') AS number FROM ' . $this->args['table'];
        $sql .= $this->_parseGroup();
        $sql .= $this->_parseWhere();
        $number = $this->conn->query($sql);
        if ($number === false) {
            return false;
        }
        return $number->number;
    }

}

/**
 * 模块异常类
 */
class HeXiModelException extends HeXiException {

}
