<?php


/**
 *
 */
class HeXi {


    /**
     * @static
     * @param string $path
     * @param string $name
     * @return HeXiWebApp
     */
    public static function WebApp($path, $name) {
        if (!is_dir(ROOT_PATH . '/' . $path)) {
            exit('WebApp "' . $name . '" Directory is not existed !');
        }
        $GLOBALS['app']['path'] = $path;
        $GLOBALS['app']['name'] = $name;
        $GLOBALS['debug']['time_begin'] = microtime(true);
        $GLOBALS['debug']['mem_begin'] = memory_get_usage();
        self::init();
        return new HeXiWebApp();
    }


    /**
     * @static
     *
     */
    protected static function init() {
        $GLOBALS['debug']['time_begin'] = microtime(true);
        $GLOBALS['debug']['mem_begin'] = memory_get_usage();
        spl_autoload_register('HeXi::loadCore');
        set_exception_handler('HeXiException::handler');
    }

    /**
     * @var array
     */
    protected static $classes = array(
        'HeXiBase'            => 'app/HeXiBase',
        'HeXiConfig'          => 'app/HeXiConfig',
        'HeXiWebApp'          => 'app/HeXiWebApp',
        'HeXiCache'           => 'cache/HeXiCache',
        'HeXiCacheAdapter'    => 'cache/HeXiCacheAdapter',
        'HeXiController'      => 'controller/HeXiController',
        'HeXiActionController'=> 'controller/HeXiActionController',
        'HeXiRestController'  => 'controller/HeXiRestController',
        'HeXiConnection'      => 'db/HeXiConnection',
        'HeXiDriver'          => 'db/driver/HeXiDriver',
        'HeXiException'       => 'debug/HeXiException',
        'HeXiDebugger'        => 'debug/HeXiDebugger',
        'HeXiLogger'          => 'debug/HeXiLogger',
        'HeXiModel'           => 'model/HeXiModel',
        'HeXiActionModel'     => 'model/HeXiActionModel',
        'HeXiModelFactory'    => 'model/HeXiModelFactory',
        'HeXiRouter'          => 'router/HeXiRouter',
        'HeXiView'            => 'view/HeXiView',
        'HeXiViewCompiler'    => 'view/HeXiViewCompiler',
        'HeXiWeb'             => 'web/HeXiWeb'
    );

    /**
     * @static
     * @param string $class
     * @throws HeXiException
     */
    protected static function loadCore($class) {
        if (!isset(self::$classes[$class])) {
            throw new HeXiException('Core Class "' . $class . '" is unsigned !');
        }
        $classFile = HEXI_PATH . self::$classes[$class] . '.php';
        if (!is_file($classFile)) {
            throw new HeXiException('Core Class "' . $class . '" is lost !');
        }
        require_once $classFile;
    }

    /**
     * @static
     * @param string $name
     * @param bool $throw
     * @return bool|mixed
     * @throws HeXiWebAppException
     */
    public static function import($name, $throw = true) {
        $path = str_replace('.', '/', $name) . '.php';
        $file = HeXiWebApp::getPath() . $path;
        if (!is_file($file)) {
            if ($throw) {
                throw new HeXiWebAppException('App File "' . $name . '" is lost !');
            }
            return false;
        }
        return require_once $file;
    }

}
