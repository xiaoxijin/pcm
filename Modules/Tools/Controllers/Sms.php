<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/7/22
 * Time: 11:27
 */
namespace Module\Tools\Controllers;
use \Module\Tools\Controller as Controller;

class Sms extends Controller
{
    public function __construct(\Xphp $xphp)
    {
        parent::__construct($xphp);
        $this->module_name='Tools';
    }
    /********************************************************************************
     *  sms内部基本功能函数部分                                                     *
     ********************************************************************************/
    private function getAuthCode(){
        return rand(1000,9999);
    }

    /********************************************************************************
     *  sms基本共用外部接口部分                                                     *
     ********************************************************************************/
    /**
     *  发送通用验证码
     */
    public function sendAuthCode($params){
        $mobile = $params['mobile'];
        $authCode = $this->getAuthCode();
        $content = "【家创易】您的验证码为".$authCode."，感谢您的使用。";
        if($this->Model("Sms")->send(array('mobile'=>$mobile,'content'=>$content))){
            $this->Model("Sms")->setAuthCode($mobile,$authCode);
        };
    }

    /**
     *  发送通用短信
     */
    public function sendSms($params){
        $mobile = $params['mobile'];
//        $authCode = $this->getAuthCode();
//        $content = "【家创易】您的验证码为".$authCode."，5分钟内有效，感谢您的使用。";
        $content = $params['content'];
        $this->Model("Sms")->send(array('mobile'=>$mobile,'content'=>$content));
    }

    /********************************************************************************
     *  sms 功能性辅助函数，查询相关数据信息                                        *
     ********************************************************************************/
    /**
     *  通过手机号码查询到设计师名字
     */
    private function get_designer_name($mobile){
        $member = model("Ucenter/member");
        $designerName = $member->get_designer_name($mobile);
        if (!empty($designerName)) return $designerName;
    }

    /********************************************************************************
     *  sms 家创易设计师通知内容                                                    *
     ********************************************************************************/
    /**
     *  短信通知设计师进行激活邮件
     */
    public function designer_verify_mail($params){
        $mobile = $params['mobile'];
        $authCode = $this->getAuthCode();
        $content = "【家创易】欢迎选择家创易，本次注册的验证码是：".$authCode."，请妥善保管。";
        if($this->Model("Sms")->send(array('mobile'=>$mobile,'content'=>$content))){
            $this->Model("Sms")->setAuthCode($mobile,$authCode);
        };
    }

    /**
     *  设计师接单范围内有业主下单通知设计师抢单
     */
    public function designer_check_order($params){
        $mobile = $params['mobile'];
        //根据手机号码查询到设计师的名字
        $designerName = $this->get_designer_name($mobile);
        $content = "【家创易】你好：".$designerName.",你的接单范围内有新设计预约的订单，请登录查看。请登入http://da.jcy.cc/user/designerGrab";
        if($this->Model("Sms")->send(array('mobile'=>$mobile,'content'=>$content))){
            //todo 可以做点啥
        };
    }

    /**
     *  业主确定约见通知设计师
     */
    public function designer_notice_meet($params){
        $mobile = $params['mobile'];
        //根据手机号码查询到设计师的名字
        $designerName = $this->get_designer_name($mobile);
        $content = "【家创易】你好：".$designerName.",业主已确定与你约谈设计。请保持电话畅通，我们的装修顾问将会尽快与你联系。";
        if($this->Model("Sms")->send(array('mobile'=>$mobile,'content'=>$content))){
            //todo 可以做点啥
        };
    }

}