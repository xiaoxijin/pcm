<?php

if(!defined("DS"))
    define("DS",DIRECTORY_SEPARATOR);
if(!defined("BS"))
    define("BS",'\\');
if(!defined("ROOT"))
    define("ROOT", __DIR__.DS);
/**
 * 框架加载器
 * @author 肖喜进
*/

class Loader
{
    /**
     * 命名空间的路径
     */
    public static $namespaces;

    /**
     * 注册一个目录下的所有子目录为顶级名称空间
     */
    static function addAllNameSpaceByDir($path){
        if (!is_dir($path))
            throw new \Exception("NAME_SPACE_NOT_FOUND");
        self::addNameSpace(BS,$path);
        foreach (scandir($path) as $file){
            if(is_dir($file) && $file!='.' && $file!='..')
                self::addNameSpace($file,$path.$file.DS);//注册service的顶级名称空间
        }
    }
    /**
     * 自动载入类
     * @param $class
     */
    static function autoload($class)
    {
        $root = explode(BS, trim($class, BS),2);
        if(count($root)==1){
            self::importFileByNameSpace(BS,$root[0]);
        }
        elseif (count($root)>1)
        {
            self::importFileByNameSpace($root[0],$root[1]);
        }
    }

    static function importFileByNameSpace($namespace_name,$file_name){

        if(!isset(self::$namespaces[$namespace_name])){
            self::addNameSpace($namespace_name,ROOT.$namespace_name.DS);
        }
        $file_path = self::$namespaces[$namespace_name].str_replace(BS, DS, $file_name).'.php';
        if(file_exists($file_path)){
            return include $file_path;
        }
        else
            return false;
    }

    static function register_autoload($load=array(__CLASS__, 'autoload'))
    {
        if(function_exists('spl_autoload_register')){
            return spl_autoload_register($load);
        }else{
            return false;
        }
    }

    static function unregister_autoload($load=array(__CLASS__, 'autoload'))
    {
        if(function_exists('spl_autoload_register')){
            return spl_autoload_unregister($load);
        }else{
            return false;
        }
    }

    /**
     * 增加根命名空间
     * @param $root
     * @param $path
     */
    static function addNameSpace($root, $path)
    {
        if(!isset(self::$namespaces[$root]))
            self::$namespaces[$root]= $path;
    }

    /**
     * 设置根命名空间
     * @param $root
     * @param $path
     */
    static function setNameSpace($root, $path)
    {
        self::$namespaces[$root]= $path;
    }

}