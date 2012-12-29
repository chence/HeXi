<?php
/**
 * Created by JetBrains PhpStorm.
 * User: FuXiaoHei
 * Date: 12-12-29
 * Time: 下午11:06
 * To change this template use File | Settings | File Templates.
 * @property Input input
 */
class Base_Controller extends Base_Class{

   public function __get($key){
       switch($key){
           case 'input':
               $this->input = Input::instance();
               return $this->input;
       }
   }
}
