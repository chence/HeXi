<?php
/**
 * Created by JetBrains PhpStorm.
 * User: FuXiaoHei
 * Date: 12-9-7
 * Time: 下午2:16
 */

function import($classStr) {
    HeXi::import($classStr);
}

function config($configStr) {
    $string = 'return HeXi::$config["'.str_replace('.','"]["',$configStr).'"];';
    return eval($string);
}

function error($message) {
    HeXi::error($message);
}