<?php

define("DS",DIRECTORY_SEPARATOR);
define("BS",'\\');

define("ROOT", __DIR__.DS);
error_reporting(E_ALL);
//error_reporting(E_ALL ^ E_NOTICE);

//将当前目录作为Xphp命名空间的初始化根目录
require('Loader.php');//加载框架自动加载类库
\Loader::register_autoload();
\Loader::addAllNameSpaceByDir(ROOT);//注册service的顶级名称空间


function getCache($key){
    return \Data::getInstance()->source("Cache")->get($key);
}

function setCache($key, $value, $expire=0){
    return \Data::getInstance()->source("Cache")->set($key, $value, $expire);
}

function delCache($key){
    return \Data::getInstance()->source("Cache")->get($key);
}

/*
 * $path_info : 请求服务路由
 * $params ：act参数， 如果没`有，则默认为寻找服务类名
 */
function service($path_info,$params=''){
    return \Bootstrap::getInstance("service")->run($path_info,$params);
}

function lib($lib_name){
    return \Factory::getInstance()->getProduct("lib",$lib_name);
}

function xphpExceptionHandler($exception) {
    echo $exception->getMessage();
}

// 设置自定义的异常处理函数
set_exception_handler("xphpExceptionHandler");

function xphpErrorExceptionHandler($errno, $errstr, $errfile, $errline ) {

    throw new \ErrorException('SYSTEM_ERROR');
}
//设置自定义的错误处理函数

set_error_handler("xphpErrorExceptionHandler");

//var_dump(service('gym/get',['is_default'=>'1']));exit;
\Bootstrap::getInstance(PHP_SAPI)->run();


