<?php
/**
 * 模型类
 * @author fuxiaohei
 * @copyright 12-8-14 fuxiaohei
 *
 */
abstract class model extends hexi {

    /**
     * 表名称
     * @var string
     */
    protected $table;

    /**
     * 初始化
     * @param string $table
     */
    public function __construct($table) {
        $this->table = $table;
        if ($this->_config('model', 'auto_pdo')) {
            $this->_pdo();
        }
    }

    /**
     * 数据库对象
     * @var PDO
     */
    protected $pdo;

    /**
     * 获取数据库
     * @param string $name
     * @return PDO
     */
    protected function _pdo($name = 'default') {
        if (!$this->pdo instanceof PDO) {
            if ($GLOBALS['pdo'][$name] instanceof PDO) {
                $this->pdo = $GLOBALS['pdo'][$name];
                return $this->pdo;
            }
            $config = $this->_config('database_' . $name, true);
            $option = array(
                PDO::ATTR_ERRMODE           => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE=> PDO::FETCH_OBJ
            );
            if (strstr($config['dsn'], 'mysql')) {
                $option[PDO::MYSQL_ATTR_INIT_COMMAND] = "set names '" . $config['charset'] . "'";
            }
            $this->pdo = new PDO($config['dsn'], $config['user'], $config['password'], $option);
            $GLOBALS['pdo'][$name] = $this->pdo;
        }
        return $this->pdo;
    }

    /**
     * 映射数据
     * @param array $map
     * @return array
     */
    protected function _parse_map($map) {
        $data = array();
        foreach ($map as $name=> $m) {
            $m = explode(':', $m);
            if (count($m) == 2) {
                $value = null;
                if ($m[0] == 'session') {
                    $value = web::init()->session($m[1]);
                } elseif ($m[0] == 'cookie') {
                    $value = web::init()->cookie($m[1], null);
                } elseif ($m[0] == 'sever') {
                    $value = web::init()->server($m[1]);
                } elseif ($m[0] == 'action') {
                    $value = $this->_action($m[1]);
                } elseif ($m[0] == 'param') {
                    $value = $this->_url_param($m[1]);
                } else {
                    $value = web::init()->input($m[1]);
                }
                $data[':' . $name] = $value;
                continue;
            }
            $data[':' . $name] = web::init()->init($m[0]);
        }
        return $data;
    }

    /**
     * 解析条件
     * @param string $condition
     * @return string
     */
    protected function _parse_condition($condition) {
        $string = '';
        if ($condition['group']) {
            $string .= ' GROUP BY ' . $condition['group'];
        }
        if ($condition['where']) {
            if (is_array($condition['where'])) {
                $string .= ' WHERE ' . join(' AND ', $condition['where']);
            } else {
                $string .= ' WHERE ' . $condition['where'];
            }
        }
        if ($condition['order']) {
            $string .= ' ORDER BY ' . $condition['order'];
        }
        if ($condition['limit']) {
            $string .= ' LIMIT ' . $condition['limit'];
        } elseif ($condition['page']) {
            $p = explode(',', $condition['page']);
            $string .= ' LIMIT ' . ($p[0] - 1) * $p[1] . ',' . $p[1];
        } else {
            $string .= ' LIMIT 1';
        }
        return $string;
    }

    /**
     * 查询数据
     * @param string $items
     * @param array $condition
     * @return mixed
     */
    public function find($items, $condition = array()) {
        $sql = "SELECT " . $items . ' FROM ' . $this->table;
        $condition['limit'] = false;
        $condition['page'] = false;
        $sql .= $this->_parse_condition($condition);
        $stmt = $this->pdo->query($sql);
        return $stmt->fetch();
    }

    /**
     * 查询数据
     * @param string $items
     * @param array $condition
     * @return array
     */
    public function find_all($items, $condition = array()) {
        $sql = "SELECT " . $items . ' FROM ' . $this->table;
        $sql .= $this->_parse_condition($condition);
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll();
    }

    /**
     * 更新数据
     * @param array $map
     * @param array $condition
     * @return bool
     */
    public function update($map, $condition) {
        $items = array_keys($map);
        $sql = "UPDATE " . $this->table . ' SET ';
        foreach ($items as $item) {
            $sql .= $item . ' = :' . $item . ' , ';
        }
        $sql = rtrim($sql, ' , ');
        if ($condition['where']) {
            if (is_array($condition['where'])) {
                $sql .= ' WHERE ' . join(' AND ', $condition['where']);
            } else {
                $sql .= ' WHERE ' . $condition['where'];
            }
        }
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($this->_parse_map($map));
    }

    /**
     * 删除数据
     * @param array $condition
     * @return int
     */
    public function delete($condition) {
        $sql = "DELETE FROM {$this->table}";
        if (is_array($condition['where'])) {
            $sql .= ' WHERE ' . join(' AND ', $condition['where']);
        } else {
            $sql .= ' WHERE ' . $condition['where'];
        }
        return $this->pdo->exec($sql);
    }

    /**
     * 添加数据
     * @param array $map
     * @return bool|string
     */
    public function insert($map) {
        $items = array_keys($map);
        $sql = "INSERT INTO " . $this->table . '(' . join(',', $items) . ') VALUES (:' . join(',:', $items) . ')';
        $stmt = $this->pdo->prepare($sql);
        if ($stmt->execute($this->_parse_map($map))) {
            return $this->pdo->lastInsertId();
        }
        return false;
    }

    /**
     * 连接到数据库
     * @return model
     */
    public function pdo() {
        if (!$this->pdo) {
            $this->_pdo();
        }
        return $this->_pdo();
    }

}
