<?php




define("DS",DIRECTORY_SEPARATOR);
define("BS",'\\');

define("SERVER_ROOT", dirname(__DIR__).DS);
define("FRAME_ROOT", __DIR__);

error_reporting(E_ALL);
//error_reporting(E_ALL ^ E_NOTICE);

//将当前目录作为Xphp命名空间的初始化根目录
require('Loader.php');//加载框架自动加载类库
Loader::initAutoLoad();//初始化自动加载函数和名称空间


function getCfg($key){
    return \Xphp\Data::getInstance()->$key;
}


function getCache($key){
    return \Xphp\Data::getInstance()->data("Cache")->get($key);
}

function setCache($key, $value, $expire=0){
    return \Xphp\Data::getInstance()->data("Cache")->set($key, $value, $expire);
}

function delCache($key){
    return \Xphp\Data::getInstance()->data("Cache")->get($key);
}


function service($name){
    return \Xphp\Factory::getInstance()->getProduct("api",$name);
}

//function controller($controller_name){
//    return \Xphp\Factory::getInstance()->getProduct("ctl",$controller_name);
//}
//
//function model($model_name){
//    return \Xphp\Factory::getInstance()->getProduct("mdl",$model_name);
//}
//
//function lib($lib_name){
//    return \Xphp\Factory::getInstance()->getProduct("lib",$lib_name);
//}

//\Xphp\Bootstrap::getInstance(PHP_SAPI)->run();
$task['api']['name'] = 'ucenter/member/getHeadMessage';
$task['api']['params'] = array(
    'uid'=>63
);
\Xphp\Bootstrap::getInstance("api")->run($task);

