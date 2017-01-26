<?php

$redis[\Xphp\Config::$default_env_name]['master'] = array(
    'host'    => "2d1bdaa03900477d.m.cnsza.kvstore.aliyuncs.com",
    'port'    => 6379,
    'password' => '2d1bdaa03900477d:JcyXxj1024',
    'timeout' => 0.25,
    'pconnect' => false,
//    'database' => 1,
);


$redis['dev']['master'] = array(
    'host'    => "192.168.1.16",
);
$redis['local']=$redis['dev'];
return $redis;






