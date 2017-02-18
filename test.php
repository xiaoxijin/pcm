<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/2/13
 * Time: 11:52
 */
$url = 'http://username:password@hostname/path?arg=value#anchor';
print_r(parse_url($url));
echo parse_url($url, PHP_URL_PATH);
//define("DS",DIRECTORY_SEPARATOR);
//define("BS",'\\');
//
//define("ROOT", __DIR__.DS);
//error_reporting(E_ALL);
////error_reporting(E_ALL ^ E_NOTICE);
//
////将当前目录作为Xphp命名空间的初始化根目录
//require('Loader.php');//加载框架自动加载类库
//\Loader::register_autoload();
//\Loader::addAllNameSpaceByDir(ROOT);//注册service的顶级名称空间
//
//$ser = new \Server\Tcp("0.0.0.0", 9566);
//$ser->set(['daemonize'=>0]);
//$ser->Start();