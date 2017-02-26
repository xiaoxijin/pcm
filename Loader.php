<?php
error_reporting(E_ALL);
//error_reporting(E_ALL ^ E_NOTICE);
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

    static public function importAllFiles(){

    }
    /**
     * 注册一个目录下的所有子目录为顶级名称空间
     */
    static function addAllNameSpaceByDir($path){
        if (!is_dir($path))
            throw new \Exception("NAME_SPACE_NOT_FOUND");
        self::addNameSpace(BS,$path);
        foreach (scandir($path) as $file){
            if(is_dir($path.$file) && $file!='.' && $file!='..')
                self::addNameSpace($file,$path.$file.DS);//注册service的顶级名称空间
        }
    }
    /**
     * 自动载入类
     * @param $class
     */
    static function autoload($class)
    {
        $namespace='';
        $file='';
        $root = explode(BS, trim($class, BS),2);
        if(count($root)==1){
            $namespace = BS;
            $file = $root[0];
        }
        elseif (count($root)>1)
        {
            $namespace = $root[0];
            $file = $root[1];
        }

        if(!self::importFileByNameSpace($namespace,$file)){
            throw new \Exception('AUTOLOAD_NOT_FOUND');
        }
    }

    static function importFileByNameSpace($namespace_name,$file_name){

//        if(!isset(self::$namespaces[$namespace_name])){
//            if($namespace_name=='\\')
//                self::addNameSpace($namespace_name,ROOT);
//            else
//                self::addNameSpace($namespace_name,ROOT.$namespace_name.DS);
//        }


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

\Loader::register_autoload();
\Loader::addAllNameSpaceByDir(ROOT);
//将当前目录作为Xphp命名空间的初始化根目录
function pushFailedMsg($msg){
    array_push(\Service::$failed_msg_history,$msg);
    return false;
}

function popFailedMsg(){
    return array_pop(\Service::$failed_msg_history);
}

function cleanPackEnv(){
    \Service::$failed_msg_history=[];
//    \Bootstrap\Service::$service_history=[];
}
/*
 * $path_info : 请求服务路由
 * $params ：act参数， 如果没`有，则默认为寻找服务类名
 */
function service($path_info,$params=''){
    return \Service::getInstance()->run($path_info,$params);
}

function lib($lib_name){
    return \Factory::getInstance()->getProduct("lib",$lib_name);
}

function xphpExceptionHandler($exception) {
    echo $exception->getMessage();
}

 //设置自定义的异常处理函数
set_exception_handler("xphpExceptionHandler");

function xphpErrorExceptionHandler($errno, $errstr, $errfile, $errline ) {
//    throw new \ErrorException($errstr, 0, $errno, $errfile, $errline);
    throw new \ErrorException('SYSTEM_ERROR');
}
//设置自定义的错误处理函数

set_error_handler("xphpErrorExceptionHandler");