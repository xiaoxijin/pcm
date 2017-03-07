<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/2/27
 * Time: 21:28
 */
class Task
{

    static private $task;
    private function __construct(){}

    static public function getInstance(){
        if(self::$task)
            return self::$task;
        self::$task=new self();
        return self::$task;
    }

    public function run($data){

        if(!isset($data['api']['name']) || empty($data['api']['name']))
            $data["result"] = \Packet::packFormat('PARAM_ERR');
        else
            $data["result"] = $this->runService($data['api']['name'],$data['api']['params']??[]);
        return $data;
    }

    public function runService($path_info,$params=[]){
        $ret_data=\Tool::timestamp();
        try {

            $ret = \Service::getInstance()->run($path_info,$params);
            if($ret){
                if(!is_array($ret))
                    $ret = $ret_data;
                else
                    $ret['timestamp']=$ret_data['timestamp'];
                $result = \Packet::packFormat('OK',$ret);
            }elseif ($ret===null){
                $result = \Packet::packFormat('USER_ERROR', $ret_data,'服务返回值为null');
            }
            else{
                $result = \Packet::packFormat('USER_ERROR', $ret_data,popFailedMsg());
            }

        } catch (\Exception | \ErrorException $e) {
            \Log::put($e->getMessage());
            $result = \Packet::packFormat($e->getMessage(),$ret_data);
        }
        cleanPackEnv();
        return $result;
    }
}