<?php
/**
 * Date: 2017/1/9
 * Time: 12:53
 */

namespace Server;

class Subscribe extends Process{

    public $name='Subscribe';
    public function run($worker){
        parent::run($worker);
        $channel_name_arr = array("__keyevent@0__:expired");
        $redis = new \Client\Redis('master');
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