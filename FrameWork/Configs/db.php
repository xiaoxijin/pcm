<?php
$db[\Xphp\Config::$default_env_name]['master']=array(
    'type'       => \Xphp\Data::TYPE_MYSQLi,
    'port'       => 3306,
    'dbms'       => 'mysql',
//    'engine'     => 'MyISAM',
    'engine'     => 'InnoDB',
    'name'       => "jcy",
    'charset'    => "utf8",
    'host'       => "jcydatabase00001.mysql.rds.aliyuncs.com",
    'user'       => "jcy_db_manager",
    'passwd'     => "qiuerxzbmnv2323GH",
    'setname'    => true,
    'persistent' => false, //MySQL长连接
    'use_proxy'  => false  //启动读写分离Proxy
);

$db['dev']['master'] = array(
    'host'       => "192.168.1.16",
    'user'       => "root",
    'passwd'     => "root1234",
);

$db['test']['master'] = array(
    'name'       => "jcy_db_test",
);

$db['local']['master'] = array(
    'host'       => "127.0.0.1",
    'user'       => "root",
    'passwd'     => "root1234",
);

return $db;