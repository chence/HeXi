<?php
require_once 'HeXiModel.php';

/**
 * 模型工厂类
 */
class HeXiModelFactory {

    /**
     * 模型对象
     * @var array
     */
    protected static $instance;

    /**
     * 生成一个模型，注意是单例
     * @static
     * @param string $modelName
     * @return HeXiModel|HeXiActionModel
     * @throws HeXiModelException
     */
    public static function factory($modelName) {
        if (!self::$instance[$modelName] instanceof $modelName) {
            HeXi::import('model.' . $modelName);
            $model = new $modelName();
            if (!$model instanceof HeXiModel) {
                throw new HeXiModelException($modelName . ' is invalid Model !');
            }
            self::$instance[$modelName] = $model;
        }
        return self::$instance[$modelName];
    }
}
