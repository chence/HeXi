<?php
/**
 * 模型类
 * @author FuXiaoHei
 */
class Model {

    /**
     * 模型对象
     * @var array
     */
    protected static $obj = array();

    /**
     * 获取某个模型
     * @param string $name 名称
     * @return Model
     */
    public static final function get($name) {
        if (!self::$obj[$name]) {
            $modelFile = config('model.path') . $name . 'Model.php';
            if (!is_file($modelFile)) {
                error('无法加载模型类文件 "' . $name . 'Model"');
            }
            require_once $modelFile;
            if (!class_exists($name . 'Model', false)) {
                error('无法加载模型类 "' . $name . 'Model"');
            }
            $modelName = $name . 'Model';
            self::$obj[$name] = new $modelName();
        }
        return self::$obj[$name];
    }
}

/**
 * 模型类抽象类
 */
abstract class abstractModel {
    /**
     * 数据库连接
     * @var DbPDO
     */
    protected $conn;

    /**
     * 表名称
     * @var string
     */
    protected $table;

    /**
     * where条件
     * @var string
     */
    protected $condition;

    /**
     * join条件和on条件
     * @var array
     */
    protected $join;

    /**
     * 数量偏移
     * @var int
     */
    protected $offset;

    /**
     * 查询数量限制
     * @var int
     */
    protected $limit;

    /**
     * 分页限制，页码和项数
     * @var array
     */
    protected $page;

    /**
     * 排序条件
     * @var string
     */
    protected $order;

    /**
     * 字段限制
     * @var string
     */
    protected $fields;

    /**
     * 初始化
     */
    public function __construct() {
        #重设属性
        $this->reset();
        #自动加载数据库
        if (config('model.database')) {
            $this->conn = Db::get();
        }
    }

    /**
     * 重设属性
     * 不处理表名称
     * @return Model
     */
    protected function reset() {
        $this->table = '';
        $this->condition = '';
        $this->join = array();
        $this->offset = 0;
        $this->page = array();
        $this->order = '';
        $this->fields = '';
        return $this;
    }

    /**
     * 添加数据
     * @param array $data
     * @return bool|string
     */
    protected function insert($data) {
        if (!$this->table) {
            error('没有设置操作的表名称');
        }
        if ($this->fields == '*') {
            error('INSERT，添加数据的字段设置错误');
        }
        $keys = explode(',', $this->fields);
        if (count($keys) != count($data)) {
            error('INSERT，添加的数据和字段不匹配');
        }
        $sql = "INSERT INTO {$this->table}({$this->fields}) VALUES (:";
        $sql .= join(':,', $keys) . ')';
        #数据库类要求key/val格式的数组，所以按顺序组合数据和字段名称
        $data = array_combine($keys, $data);
        return $this->conn->prepareExec($sql, $data, true);
    }

    /**
     * 删除数据
     * @return int
     */
    protected function delete() {
        if (!$this->table) {
            error('没有设置操作的表名称');
        }
        if (!$this->condition) {
            error('DELETE，必须设置删除的WHERE限制条件');
        }
        $sql = "DELETE FROM {$this->table} WHERE {$this->condition}";
        return $this->conn->exec($sql);
    }

    /**
     * 查询数据
     * @return mixed
     */
    protected function find() {
        if (!$this->table) {
            error('没有设置操作的表名称');
        }
        $sql = "SELECT {$this->fields} FROM {$this->table} ";
        #关联查询
        if ($this->join) {
            $sql .= "JOIN {$this->join[0]} ON {$this->join[1]} ";
        }
        if ($this->condition) {
            $sql .= "WHERE {$this->condition} ";
        }
        if ($this->order) {
            $sql .= "ORDER BY {$this->order}";
        }
        #查询一个结果，考虑偏移的问题
        if ($this->offset > 0) {
            $sql .= "LIMIT 1 OFFSET {$this->offset}";
        } else {
            $sql .= "LIMIT 1";
        }
        return $this->conn->query($sql);
    }

    /**
     * 查询一组数据
     * @return array
     */
    protected function findAll() {
        if (!$this->table) {
            error('没有设置操作的表名称');
        }
        $sql = "SELECT {$this->fields} FROM {$this->table} ";
        if ($this->join) {
            $sql .= "JOIN {$this->join[0]} ON {$this->join[1]} ";
        }
        if ($this->condition) {
            $sql .= "WHERE {$this->condition} ";
        }
        if ($this->order) {
            $sql .= "ORDER BY {$this->order}";
        }
        if ($this->page) {
            $sql .= "LIMIT " . ($this->page[0] - 1) * $this->page[1] . ',' . $this->page[1];
        } else {
            $sql .= "LIMIT {$this->limit} ";
            if ($this->offset > 0) {
                $sql .= "OFFSET {$this->offset}";
            }
        }
        return $this->conn->queryAll($sql);
    }

    /**
     * 统计字段
     * @param string $key 字段名称
     * @return bool|int
     */
    protected function count($key) {
        if (!$this->table) {
            error('没有设置操作的表名称');
        }
        $this->fields = 'count(' . $key . ') AS number';
        $result = $this->find();
        return $result === false ? false : (int)$result->number;
    }

    /**
     * 更新数据
     * @param array $data
     * @return bool|string
     */
    protected function update($data) {
        if (!$this->table) {
            error('没有设置操作的表名称');
        }
        if ($this->fields == '*') {
            error('UPDATE，更新数据的字段设置错误');
        }
        $keys = explode(',', $this->fields);
        if (count($keys) != count($data)) {
            error('UPDATE，更新的数据和字段不匹配');
        }
        $sql = "UPDATE {$this->table} SET ";
        foreach ($keys as $k) {
            $sql .= $k . ' = :' . $k . ',';
        }
        $sql = rtrim($sql, ',');
        if ($this->condition) {
            $sql .= " WHERE {$this->condition}";
        }
        $data = array_combine($keys, $data);
        return $this->conn->prepareExec($sql, $data);
    }
}
