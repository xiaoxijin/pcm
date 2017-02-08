<?php

if(!defined("DS"))
    define("DS",DIRECTORY_SEPARATOR);
if(!defined("BS"))
    define("BS",'\\');

/**
 * 框架加载器
 * @author 肖喜进
 * @package XPhpSystem
 * @subpackage base
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
        self::addNameSpace(DS,$path);
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
        if(!self::load($class))
            throw new \Exception("AUTOLOAD_NOT_FOUNT");
    }

    static function load($class){
        $root = explode(BS, trim($class, BS),2);
        if(count($root)==1 && isset(self::$namespaces[DS])){
            return self::loadFile(DS,$root[0]);
        }
        elseif (count($root) > 1 && isset(self::$namespaces[$root[0]]))
        {
            return self::loadFile($root[0],$root[1]);
        }else{
            return false;
        }
    }

    static function loadFile($namespace_name,$class_name){
        if(file_exists(self::$namespaces[$namespace_name].str_replace(BS, DS, $class_name).'.php')){
            include self::$namespaces[$namespace_name].str_replace(BS, DS, $class_name).'.php';
            return true;
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
     * 设置根命名空间
     * @param $root
     * @param $path
     */
    static function addNameSpace($root='Xphp', $path=__DIR__)
    {
        self::$namespaces[$root] = $path;
    }

}