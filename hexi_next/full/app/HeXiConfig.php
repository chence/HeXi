<?php
/**
 * 配置类
 */
class HeXiConfig {


    /**
     * 已经加载的配置
     * @var array
     */
    protected static $hasImport = array();

    /**
     * 加载配置
     * @static
     * @param string $configName 名称
     * @param bool $reload 是否重新加载
     * @return array
     * @throws HeXiConfigException
     */
    public static function import($configName, $reload = false) {
        if (!$reload && in_array($configName, self::$hasImport)) {
            return true;
        }
        #获取配置文件
        $configFile = HeXiWebApp::getPath() . 'config/' . $configName . '.ini';
        if (!is_file($configFile)) {
            throw new HeXiConfigException('Configuration File "' . $configFile . '" is lost !');
        }
        #引入和解析配置数据
        $data = parse_ini_file($configFile, true);
        self::$hasImport[] = $configName;
        return self::parseConfig($data);
    }

    /**
     * 解析配置数据
     * @static
     * @param array $data
     * @return mixed
     */
    private static function parseConfig($data) {
        foreach ($data as $key=> $value) {
            #注意转换类型，支持数值和布尔值
            $GLOBALS['config'][$key] = self::convertType($value);
        }
        return $data;
    }

    /**
     * 处理配置数据类型
     * @static
     * @param array $data
     * @return array|bool
     */
    private static function convertType($data) {
        #循环处理
        if (is_array($data)) {
            foreach ($data as $k=> $v) {
                $data[$k] = self::convertType($v);
            }
            return $data;
        }
        #转换为数值
        if (is_numeric($data)) {
            return $data + 0;
        }
        #转换bool值
        if ($data === 'true') {
            return true;
        }
        if ($data === 'false') {
            return false;
        }
        #字符串，不转换
        return $data;
    }


    /**
     * 获取配置信息
     * @static
     * @param string $key
     * @param string $value
     * @return mixed
     */
    public static function get($key, $value) {
        if (!isset($GLOBALS['config'][$key][$value])) {
            return null;
        }
        return $GLOBALS['config'][$key][$value];
    }
}

/**
 * 配置异常类
 */
class HeXiConfigException extends HeXiException {

}
