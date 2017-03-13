<?php


require_once(__DIR__ .DIRECTORY_SEPARATOR.'Loader.php');//加载框架
\Cfg::setEnvName();
\Packet::$ret = \Cfg::get("ret");
\Packet::$task_type = \Cfg::get("rpc.tasktype");

\Cache::set('123','123123');
//$data = \Task::getInstance()->runService("index");

//$data = \Task::getInstance()->runService("getCourseListByDate", [
//    'ret_key'=>'courseList',
//    'gymId'=>1,
//    'date'=>'2017-02-28'
//]);
var_dump($data);
exit;