<?php

/**
 * XPHP加载器
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
     * 初始化自动加载类
     */
    static function initAutoLoad($root='Xphp', $path=__DIR__){
        self::addNameSpace($root, $path);
        self::register_autoload();
    }
    /**
     * 自动载入类
     * @param $class
     */
    static function autoload($class)
    {

        $root = explode('\\', trim($class, '\\'),2);
        if (count($root) > 1 and isset(self::$namespaces[$root[0]]))
        {
            if(file_exists(self::$namespaces[$root[0]].'/'.str_replace('\\', '/', $root[1]).'.php'))
                include self::$namespaces[$root[0]].'/'.str_replace('\\', '/', $root[1]).'.php';
            else
                throw new \Xphp\Exception\NotFound();
        }
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