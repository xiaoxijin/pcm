<?php

namespace Module\Timer\Controllers;


class OrderStatusChange extends \Xphp\Controller
{
    public function gettest($params){
        var_dump($params);
//        $redis_subscribe = new \swoole_process(array($this, "subscribe"));
//        $this->xphp->serv->addProcess($redis_subscribe);
    }


    public function subscribe(\swoole_process $process){

//        swoole_set_process_name("doraProcess|EmailTimer");
//        $redis = new \Redis();
//        $redis->connect('192.168.1.16',6379);
//        $redis->auth("2d1bdaa03900477d:JcyXxj1024");
//        $channel = array("__keyevent@0__:expired");
//        $redis->subscribe($channel, array($this,'callback'));

    }

    public function callback($instance, $channelName, $message) {
//        echo $channelName, "==>", $message,PHP_EOL;
    }
}
