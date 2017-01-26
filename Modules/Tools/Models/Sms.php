<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/7/24
 * Time: 13:29
 */

namespace Module\Tools\Models;
use \Module\Tools\Model as Model;

class Sms extends Model
{
    /********************************************************************************
     * 发送短信                                             *
     ********************************************************************************/
    /**
     * @param $params array('mobile'=>  ,'content'=> ) 一个包含发送到那个手机号  和 内容的数组
     * @return bool
     */
    public function send($params)
    {
        $smsConfig =$this->config['sms']['master'];
        $request_url = $smsConfig['gateway']."action=send&userid={$smsConfig['userid']}&account={$smsConfig['account']}&password={$smsConfig['password']}&mobile={$params['mobile']}&content={$params['content']}&sendTime=";
        $result = file_get_contents($request_url);
        $result_xml = simplexml_load_string($result);

        if($result_xml->returnstatus=="Success")
            return true;
//		echo "返回状态为：".$result_xml->returnstatus."<br>";
//		echo "返回信息：".$result_xml->message."<br>";
//		echo "返回余额：".$result_xml->remainpoint."<br>";
//		echo "返回本次任务ID：".$result_xml->taskID."<br>";
//		echo "返回成功短信数：".$result_xml->successCounts."<br>";
//		echo "<br>";
//		echo "<br>";
        return false;
    }


    public function setAuthCode($mobile,$authCode){
        $this->redis->setex($mobile,60,$authCode);
    }

    public function getAuthCode($mobile){
        return $this->redis->get($mobile);
    }

    public function clearAuthCode($mobile){
        $this->redis->del($mobile);
    }
}