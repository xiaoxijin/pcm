<?php

//$a = $bb['sss']['ccc']??'adf';
//var_dump($a);
//function aa($a,& $b=0){
//    $b++;
//    bb($a,$b);
//}
//function bb($c,& $b=0){
//    $b++;
//}
//////
//aa('123',$c);
//var_dump($c);
require_once(__DIR__ .DIRECTORY_SEPARATOR.'Loader.php');//加载框架


\Cfg::setEnvName();
\Packet::$ret = \Cfg::get("ret");
\Packet::$task_type = \Cfg::get("rpc.tasktype");
$data = \Task::getInstance()->runService("index");

//$data = \Task::getInstance()->runService("getCourseListByDate", [
//    'ret_key'=>'courseList',
//    'gymId'=>1,
//    'date'=>'2017-02-28'
//]);
var_dump($data);
exit;