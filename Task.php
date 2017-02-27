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
            $data["result"] = \Packet::packFormat('PARAM_ERR','exception');
        else
            $data["result"] = $this->runService($data['api']['name'],$data['api']['params']??'');
        return $data;
    }

    public function runService($path_info,$params=''){
        try {

            $ret = $this->doServiceWork($path_info,$params);
            if($ret){
                $ret['timestamp']=time()*1000;
                $result = \Packet::packFormat('OK',$ret);
            }
            else{
                $ret['timestamp']=time()*1000;
                $result = \Packet::packFormat('USER_ERROR', $ret,popFailedMsg());
            }

        } catch (\Exception | \ErrorException $e) {
            $result = \Packet::packFormat($e->getMessage(),'exception');
        }
        cleanPackEnv();
        return $result;
    }

    private function doServiceWork($path_info,$params='')
    {
        return \Service::getInstance()->run($path_info,$params);
    }
}