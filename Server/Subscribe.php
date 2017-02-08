<?php
/**
 * User: xiaoxijin
 * Date: 2017/1/9
 * Time: 12:53
 */

namespace Server;

class Subscribe{

    private $mount_ser_name;
    public function __construct($mount_ser,$mount_ser_name)
    {
        $this->mount_ser_name=$mount_ser_name;
        $subscribe_expired = new \swoole_process(array($this, "SubChannel"));
        $mount_ser->addProcess($subscribe_expired);
    }

    public function SubChannel(\swoole_process $process)
    {
        swoole_set_process_name("{$this->mount_ser_name}Process|".__FUNCTION__);
        $channel_name_arr = array("__keyevent@0__:expired");
        $redis = \Data::getInstance()->data("subRedis");
        $redis->setOption(\Redis::OPT_READ_TIMEOUT, -1);
        $redis->subscribe($channel_name_arr, array($this, 'handleKeyEventExpired'));

    }

    public function handleKeyEventExpired($instance, $channelName, $message) {
        $params = json_decode($message,true);
        if(is_array($params))
            return true;
        $apiInfo = array();
        if(isset($params['_api']))
            $apiInfo['api'] = $params['_api'];
        else{
            if(!isset($params['name']) || !isset($params['name'])) return true;
            $apiInfo['api']['name']=$params['name'];
            $apiInfo['api']['params']=$params['params'];
        }
        \Client\Http::post($apiInfo);
    }
}