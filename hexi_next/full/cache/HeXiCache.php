<?php


/**
 * 缓存类
 */
class HeXiCache {

    /**
     * @var array
     */
    protected static $instance;

    /**
     * 缓存的类型
     * @var array
     */
    private static $typeArray = array(
        #注意后面跟的就是类的名字，和文件名字要相同
        'file'=> 'HeXiCacheFile'
    );

    /**
     * 获取一个缓存类
     * @static
     * @param string $name 缓存类的键名，根据键名找到对应的类，可能同时好几个缓存类在干活
     * @param string $type 缓存类的类型
     * @return HeXiCacheAdapter|HeXiCacheFile
     */
    public static function factory($name = 'default', $type = 'file') {
        #获取需要的缓存类型
        $cacheObjectName = self::$typeArray[$type];
        #如果已经保存了缓存类，且类型是正确的，那就返回
        #有一种情况可能要更换缓存的类型，名称不变
        if (self::$instance[$name] instanceof $cacheObjectName) {
            return self::$instance[$name];
        }
        #载入缓存类文件，就在当下文件夹里
        require_once $cacheObjectName . '.php';
        #生成缓存对象，存入全局并放回
        self::$instance[$name] = new $cacheObjectName();
        return self::$instance[$name];
    }
}

/**
 * 缓存异常类
 */
class HeXiCacheException extends HeXiException {

}
