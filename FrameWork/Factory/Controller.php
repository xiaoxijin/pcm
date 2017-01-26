<?php
namespace Xphp\Factory;

/**
 * XPHP加载器
 * @author 肖喜进
 * @package XPhpSystem
 * @subpackage base
*/

class Controller extends Object
{
    static public function load($name){
        self::$type = "Controllers";
        $class_full_name = parent::load($name);
        $ctl= new $class_full_name();
        $ctl->module_name=self::$module_name;
        return $ctl;
    }

}