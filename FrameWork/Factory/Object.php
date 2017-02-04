<?php
namespace Xphp\Factory;

/**
 * 所有Xphp应用类的基类
 * Class Object
 */

class Object
{

    protected $type;
    protected $type_path;
    protected $module_name;
    protected $file_name;
    /*
     * $module_root_name : 模块根目录名称如 Module
     * $current_module_name，$module_name :模块名称如 Tools
     * $file_name 文件名称也是类名如：Designer,Member等
     * $type 类的类型，如： Controller,Model,Lib等
     */
    public function load($name){

        $class_name = explode(BS,$name,2);
        if(count($class_name)<1)
            return false;
        $module_root = \Xphp\Bootstrap\Service::$default_module_root;
        $this->file_name = $class_name[1];
        \Xphp\Bootstrap\Service::$current_class_name = $this->file_name = $class_name[1];
        if(count($top_class = explode(":",$class_name[0]))>1){
            $module_root = $top_class[0];
            $current_module_name =$top_class[1];
        }else{
            $current_module_name = $class_name[0];
        }
        $class_full_name= BS.$module_root.BS.$current_module_name.BS.$this->type.BS.$this->file_name;
        \Xphp\Bootstrap\Service::$current_module_name = $this->module_name = $current_module_name;
        $this->type_path=\Loader::$namespaces[$module_root].DS.$current_module_name.DS.$this->type.DS;
        $file_path = $this->type_path.str_replace(BS,DS,$this->file_name).'.php';
        if(file_exists($file_path)){
            include $file_path;
            return $class_full_name;
        }
        else
            return false;
    }
}