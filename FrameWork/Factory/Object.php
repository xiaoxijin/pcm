<?php
namespace Xphp\Factory;

/**
 * 所有Xphp应用类的基类
 * Class Object
 */

class Object
{

    static protected $type;
    static protected $type_path;
    static protected $module_name;
    static protected $file_name;
    /*
     * $module_root_name : 模块根目录名称如 Module
     * $current_module_name，$module_name :模块名称如 Tools
     * $file_name 文件名称也是类名如：Designer,Member等
     * $type 类的类型，如： Controller,Model,Lib等
     */
    static public function load($name){

        $class_name = explode(BS,$name,2);
        if(count($class_name)<1)
            return false;
        $module_root = \Xphp\Bootstrap\Service::$default_module_root;
        \Xphp\Bootstrap\Service::$current_class_name = self::$file_name = $class_name[1];
        if(count($top_class = explode(":",$class_name[0]))>1){
            $module_root = $top_class[0];
            $current_module_name =$top_class[1];
        }else{
            $current_module_name = $class_name[0];
        }
        $class_full_name= BS.$module_root.BS.$current_module_name.BS.self::$type.BS.self::$file_name;
        \Xphp\Bootstrap\Service::$current_module_name = self::$module_name = $current_module_name;
        self::$type_path=\Loader::$namespaces[$module_root].DS.$current_module_name.DS.self::$type.DS;
        $file_path = self::$type_path.str_replace(BS,DS,self::$file_name).'.php';
        if(file_exists($file_path)){
            include $file_path;
            return $class_full_name;
        }
        else
            return false;
    }
}