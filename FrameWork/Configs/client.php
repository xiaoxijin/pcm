<?php
$client[\Xphp\Config::$default_env_name]['master'] = array(
    'host' => 'http://120.76.124.85',
    'http_port'=>'9566',
    'tcp_port'=>'9567',
    'path'    =>'\/api\/',
    'type'    =>"multinoresult",
);

$client['dev']['master'] =array(

    'host' => 'http://192.168.1.16',
    'http_port'=>'9576',
    'tcp_port'=>'9577',

);

$client['test']['master'] = array(
    'http_port'=>'9576',
    'tcp_port'=>'95777',
);

$client['local']=$client['dev'];
return $client;


