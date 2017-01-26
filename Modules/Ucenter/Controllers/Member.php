<?php
/**
 * Created by PhpStorm.
 * User: 肖喜进
 * Date: 2016/7/19
 * Time: 17:37
 */
namespace Module\Ucenter\Controllers;
use Xphp\Tool;


class Member extends \Xphp\Controller
{

    public function getHeadMessage($params){

        if (isset($params['uid']) && $params['uid']>0){

            $member_info= $this->Model("member")->get($params['uid']);

            $this->setRet(['data'=>$member_info]);
//            $this->setRet(array('data'=>array(
//                "designer_id"=>$params['uid'],
//                "designer_name"=>$member_info['realname'],
//                "header_pic"=>model("Tools/Cdn")->getCdnPhotoUrlByUser($member_info['face'],array(
//                    "w"=>50,"h"=>50)),
//                "orders_count"=>model("orders/Designer_yuyue")->getOrderCount($params['uid'],1),
//                "messages_count"=>(string)model("Message/manage")->count(array("receive_id"=>$params['uid'],
//                    "closed" => 0,
//                    "state" => 0,
//                )),
//                )));
        }
    }

    /**
     * 发送设计师登陆验证码
     * @param $params
     */
    public function sendDsgLoginCode($params){
        $mobile = $params['mobile'];
        $user = $this->Model("member")->chk_designer_info($mobile);
        if(Tool::isValid($user['code'])){
            return $this->setRet($user);
        }
        $authCode = rand(1000,9999);
        $content = "【家创易】您的验证码为".$authCode."，感谢您的使用。";
        if(model("tools/sms")->send(array('mobile'=>$mobile,'content'=>$content))){
            model("tools/sms")->setAuthCode($mobile,$authCode);
        };
    }


    public function chkMobileAuth($params){

        $auth_code = model("tools/sms")->getAuthCode($params['mobile']);

        $user = $this->Model("member")->chk_designer_info($params['mobile']);
        if(Tool::isValid($user['code'])){
            $this->setRet($user);
        }
        elseif(!isset($params['authCode']) || empty($params['authCode'])){
            $this->setRet(array('code'=>AUTHCODE_EMPTY));
        }
        elseif($auth_code==$params["authCode"]){
            model("tools/sms")->clearAuthCode($params['mobile']);
            $this->setRet(array('data'=>array("uid"=>$user['uid'])));
        }elseif($auth_code<>$params["authCode"]) {
            $this->setRet(array('code'=>AUTHCODE_NOTMATCH));
        }
        else{
            $this->setRet(array('code'=>AUTHCODE_OVERTIME));
        }
    }

}
