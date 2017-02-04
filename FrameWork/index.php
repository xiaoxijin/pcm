<?php
//$test=[];
//
//array_push($test,1);
//array_push($test,2);
//array_push($test,3);
//array_push($test,4);
//array_push($test,51);
//var_dump(end($test));
//var_dump($test);
//exit;
define("DS",DIRECTORY_SEPARATOR);
define("BS",'\\');

define("SERVER_ROOT", dirname(__DIR__).DS);
define("FRAME_ROOT", __DIR__);

error_reporting(E_ALL);
//error_reporting(E_ALL ^ E_NOTICE);

//将当前目录作为Xphp命名空间的初始化根目录
require('Loader.php');//加载框架自动加载类库
Loader::initAutoLoad();//初始化自动加载函数和名称空间

function setRet($msg){
    \Xphp\Bootstrap\Service::$current_ret_msg=$msg;
}

function getRet(){
    return \Xphp\Bootstrap\Service::$current_ret_msg;
}

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


/*
 * $path_info : 请求服务路由
 * $params ：act参数， 如果没有，则默认为寻找服务类名
 */
function service($path_info,$params=[]){
    return \Xphp\Bootstrap::getInstance("api")->run($path_info,$params);
}


//function controller($controller_name){
//    return \Xphp\Factory::getInstance()->getProduct("ctl",$controller_name);
//}
//
//function model($model_name){
//    return \Xphp\Factory::getInstance()->getProduct("mdl",$model_name);
//}
//
function lib($lib_name){
    return \Xphp\Factory::getInstance()->getProduct("lib",$lib_name);
}

$task['name'] = 'ucer/mem/getHeadMessage';
$task['params'] = array(
    'uid'=>63
);
var_dump(service($task));
//\Xphp\Bootstrap::getInstance(PHP_SAPI)->run();


