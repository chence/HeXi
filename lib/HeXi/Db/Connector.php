<?php

/**
 * 数据库连接类
 */
class Db_Connector {

    /**
     * 驱动对应的类名称
     * @var array
     */
    private static $class = array(
        'PDO' => 'Db_PDO'
    );


    /**
     * 连接到数据库
     * @param string $name
     * @return Db_Abstract|Db_PDO
     */
    public static function connect($name = 'default') {
        if (!HeXi::$config['database']) {
            HeXi::loadConfig('database');
        }
        $driver = strtoupper(HeXi::$config['database'][$name]['driver']);
        if (!$driver) {
            Error::stop('Database "' . $name . '" Configuration is missing !', 500);
        }
        $className = self::$class[$driver];
        if (!$className) {
            Error::stop('Database "' . $name . '" Driver is not supported !', 500);
        }
        return HeXi::instance($className, $name);
    }

    /**
     * 断开连接
     * @param string $name
     */
    public static function disConnect($name) {
        $driver = strtoupper(HeXi::$config['database'][$name]['driver']);
        HeXi::destroy(self::$class[$driver], $name);
    }
}
