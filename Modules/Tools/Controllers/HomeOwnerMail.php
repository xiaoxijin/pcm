<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/7/22
 * Time: 11:27
 */
namespace Module\Tools\Controllers;
use \Module\Tools\Controller as Controller;

class HomeOwnerMail extends Controller
{


    private  $_datetime;
    private  $_email = null;
    private  $mail;

    /********************************************************************************
     *  phpmailer 相关属性的基本函数                                                *
     ********************************************************************************/
    public function __construct(\Xphp $xphp)
    {
        parent::__construct($xphp);
        $this->initMail();
    }
    private function initMail(){

        if(is_object($this->mail))
            return true;
        $cfg = $this->config['mail']['master'];
        $mail = $this->Lib("PHPMailer");
        $mail->CharSet = 'UTF-8';
        if(strtolower($cfg['mode']) == 'smtp'){
            $mail->IsSMTP();
            $mail->Host       = $cfg['smtp']['host'];
            $mail->Port       = $cfg['smtp']['port'];
            $mail->SMTPAuth   = true;
            $mail->Username   = $cfg['smtp']['uname'];
            $mail->Password   = $cfg['smtp']['passwd'];
        }else{
            $mail->IsMail();
        }
        $mail->from_mail = $cfg['sender'];
        $mail->from_name = $cfg['title'];

        $mail->_email = $cfg['receiver'];
        $mail->_datetime = date('Y-m-d H:i:s',__TIME);

        $mail->SetFrom($mail->from_mail, $mail->from_name);
        $this->mail = $mail;
//        var_dump($mail);
    }

    private function MsgHTML($body,$basedir='')
    {
        $body = "<body>{$body}</body>";
        $this->mail->MsgHTML($body,$basedir);
    }

    private function systemmail($key = null,$data = array()){

        return $this->mail->sendmail($this->_email, '管理员通知邮件！', $key, $data);
    }

    private function sendmail($to, $title, $body)
    {
        if(!$body) return false;
//        $check = K::M('verify/check');
        if(is_array($to['to'])){
            for ($to_num=0;$to_num<count($to['to']);$to_num++)
                $this->mail->AddAddress($to['to'][$to_num]);
        }else if(is_string($to['to'])){
            $this->mail->AddAddress($to['to']);
        }else{
//            $this->errmsg = '错误的收件人地址';
//            return false;
        }

        if(is_array($to['cc'])){
            for ($to_num=0;$to_num<count($to['cc']);$to_num++)
                $this->mail->addCC($to['cc'][$to_num]);
        }else if(is_string($to['cc'])){
            $this->mail->addCC($to['cc']);
        }else{
//            $this->errmsg = '错误的收件人地址';
//            return false;
        }

        if(is_array($to['bcc'])){
            for ($to_num=0;$to_num<count($to['bcc']);$to_num++)
                $this->mail->addBCC($to['bcc'][$to_num]);
        }else if(is_string($to['bcc'])){
            $this->mail->addBCC($to['bcc']);
        }else{
//            $this->errmsg = '错误的收件人地址';
//            return false;
        }

        $this->mail->Subject = $title;
        $this->mail->AltBody = $this->mail->AltBody;
        $this->MsgHTML($body);

        return $this->send();
    }

    private function clear()
    {
        $this->mail->ClearAddresses();
        $this->mail->ClearAttachments();
    }

    private function send()
    {
        try{
            $this->mail->Send();
            $this->clear();
            return true;
        }catch(phpmailerException $e){
            $this->errmsg = $e->errorMessage();
            return false;
        }catch(Exception $e){
            $this->errmsg = $e->errorMessage();
            return false;
        }
        return false;
    }

    /********************************************************************************
     *  邮件body部分                                                                *
     ********************************************************************************/
    /**
     * 默认测试邮件接口
     */
    private function default_body(){
        $content="我测试一下,我不为空";
        return $content;
    }


    /********************************************************************************
     *  外部接口函数，邮件发送接口                                                  *
     ********************************************************************************/
    /**
     *  默认测试邮件接口
     */
    public function inviteGetOrder($params){
        $this->initMail();
        $this->sendmail($params, '您有新的约谈请求客服已经审核通过', $this->default_body());
    }



    public function getdesignerGrabOrderFinishedToHomeOwner($params){

        $content =<<<EOF

<div class="mail-box" style="max-width: 640px;
            margin: 0 auto;
            font-size: 16px;
            color: #4C4C4C;
            font-family: 'Helvetica Neue,Helvetica,Arial,sans-serif';">
    <header style="width: 100%;
                margin: 0 auto;
                border-bottom:1px solid #aaa;">
        <div class="head-box" style="width: 90%;margin: 3% auto;font-weight: bold;">
            <h5 style="margin: 0; padding:0;margin-bottom: 3%;font-size: 1.2rem;line-height: 1.5rem;">新消息/设计师抢单通知</h5>
            <p style="margin: 0;padding:0;display: inline-block;font-size: 0.9rem;line-height: 1.2rem;">家创易业主服务中心</p>
        </div>
    </header>

    <section style="width: 90%;
                margin: 0 auto;
                border-bottom: 1px solid #ccc;">
        <img class="logo-pic" src="http://st.jcy.cc/m_heade.png" style="display: block;
                width: 85%;
                max-width: 505px;
                height: 30%;
                max-height: 180px;
                margin: 10% auto;">
        <h6 style=" margin: 0;padding:0;
                font-size: 1.2rem;
                line-height: 2.0rem;">尊敬的{$params['realname']}：</h6>
        <p style="margin: 0;padding:0; font-size: 1.2rem;
                line-height: 2.0rem;">你预约的设计下午茶，有{$params['grabCount']}名设计师报名参与，请打开家创易家居微信服务号查看报名的设计师，还可能有其他设计师抢单，请耐心等待。</p>
    
        <div class="item-box" style="margin:5%  0;
                font-size: 0;">
            <h6 style="margin: 0;padding:0;font-size: 1.2rem;
                line-height: 2.0rem;">约谈详情</h6>
            <p style="margin: 0;padding:0;font-size: 1.2rem;
                line-height: 2.0rem;
                font-weight: bold;
                display: inline-block;">时间：<span style=" margin-right: 0.3rem;">{$params['date']}</span></p>
            <p style="margin: 0;padding:0;font-size: 1.2rem;
                line-height: 2.0rem;
                font-weight: bold;
                display: inline-block;">地点：{$params['address']}</p>
        </div>
    </section>

    <section style="width: 90%;
                margin: 0 auto;
                border-bottom: 1px solid #aaa;">
        <div class="item-box" style=" margin:5%  0;
                font-size: 0;">
            <h6 style="margin: 0;padding:0;font-size: 1.2rem;
                line-height: 2.0rem;">项目详情</h6>
            <p style="margin: 0;padding:0;font-size: 1.2rem;
                line-height: 2.0rem;
                font-weight: bold;
                display: inline-block;
                min-width: 50%;">订单编号：{$params['yuyue_sn']}</p>
       
            <p style="margin: 0;padding:0;font-size: 1.2rem;
                line-height: 2.0rem;
                font-weight: bold;
                display: inline-block;
                min-width: 50%;">所在地区：{$params['area_name']}</p>
            <p style="margin: 0;padding:0;font-size: 1.2rem;
                line-height: 2.0rem;
                font-weight: bold;
                display: inline-block;
                min-width: 50%;">小区名称：{$params['home_name']}</p>
            <p style="margin: 0;padding:0;font-size: 1.2rem;
                line-height: 2.0rem;
                font-weight: bold;
                display: inline-block;
                min-width: 50%;">项目类型：{$params['project_type']}</p>
            <p style="margin: 0;padding:0;font-size: 1.2rem;
                line-height: 2.0rem;
                font-weight: bold;
                display: inline-block;
                min-width: 50%;">房屋面积：{$params['area_size']}㎡</p>
        </div>
    </section>

    <footer style="width: 90%;
                margin: 0 auto;
                font-size: 0;
                margin-bottom: 10%;
                padding-top: 6%;">
        <div class="tip" style="display: inline-block;margin-top: 0.3rem;min-width: 50%;">
            <img src="http://st.jcy.cc/m_more.png" style="width: 1.2rem;
                height: 1.2rem;
                vertical-align: bottom;">
            <p style="margin: 0;padding:0;display: inline-block;
                font-size: 1rem;
                line-height: 1.2rem;
                font-weight: bold;
                color: #1a1a1a;
                margin-left: 0.6rem;">了解更多<span style="margin-left: 0.6rem;"><a href="http://www.jcy.cc" style="color: #009DF5;">http://www.jcy.cc</a></span></p>
        </div>
        <div class="tip" style="display: inline-block;margin-top: 0.3rem;min-width: 50%;">
            <img src="http://st.jcy.cc/m_phone.png" style="width: 1.2rem;
                height: 1.2rem;
                vertical-align: bottom;">
            <p style="margin: 0;padding:0;display: inline-block;
                font-size: 1rem;
                line-height: 1.2rem;
                font-weight: bold;
                color: #1a1a1a;
                margin-left: 0.6rem;">全国咨询电话<span style="margin-left: 0.6rem;">400 838 2323</span></p>
        </div>
        <div class="copyright" style="margin-top: 5%;">
            <p class="chinese" style="margin: 0;padding:0;text-align: center;
                font-size: 0.9rem;
                line-height: 1.5rem;
                color: #969696;">
                <span>此邮件为系统邮件/请勿回复</span>
                <span>深圳市家创易科技发展有限公司版权所有</span>
            </p>
            <p class="english" style="margin: 0;padding:0;text-align: center;
                line-height: 1.5rem;
                color: #969696;
                font-size: 0.8rem;">To unsubscribe from this message click here.Or to unsubscribe from all future emails click here</p>
        </div>

    </footer>

</div>

EOF;

        return $content;
    }

    public function designerGrabOrderFinishedToHomeOwner($params){

        $this->initMail();
        $this->sendmail(array('to'=>$params['to']), '抢单完成通知', $this->getdesignerGrabOrderFinishedToHomeOwner($params));
    }




    public function getuserActivityNoticePage($params){

        $content =<<<EOF

<div class="mail-box" style="max-width: 640px;
            margin: 0 auto;
            font-size: 16px;
            color: #4C4C4C;
            font-family: 'Helvetica Neue,Helvetica,Arial,sans-serif';">
<header style="border-bottom:1px solid #aaa;">
    <div class="head-box" style="width: 90%;margin: 3% auto;font-weight: bold;">
        <h5 style="margin: 0; padding:0;margin-bottom: 3%;font-size: 1.2rem;line-height: 1.5rem;">新消息/激活邮箱通知</h5>
        <p style="margin: 0;padding:0;display: inline-block;font-size: 0.9rem;line-height: 1.2rem;">家创易业主服务中心</p>
        <a href="http://www.jcy.cc" style="float: right;font-size: 0.9rem;line-height: 1.2rem;text-decoration: none;color: #009DF5;">详情</a>
    </div>
</header>

<section style="width: 90%;
            margin: 0 auto;">
    <img class="logo-pic" src="http://st.jcy.cc/img/m_heade.png" style="display: block;
            width: 85%;
            max-width: 505px;
            height: 30%;
            max-height: 180px;
            margin: 10% auto;">
    <h6 style="margin: 0;
            padding:0;
            font-size: 1.2rem;
            line-height: 2.0rem;">尊敬的{$params['realname']}：</h6>
    <p style="margin: 0;padding:0;font-size: 1.2rem;
            line-height: 2.0rem;">欢迎选择家创易，为了让你更好的了解服务进度，请点击以下按钮登录邮箱，激活账号：</p>
    <a class="btn" href="{$params['verifyUrl']}" style=" display: block;
            width:90%;
            height: 3.9rem;
            margin: 10% auto;
            text-align: center;
            text-decoration: none !important;
            font-size: 1.4rem;
            font-weight: bold;
            color: #ddd;
            line-height: 3.9rem;
            background-color:#222;
            border-radius: 5px;">点此激活</a>
</section>

<footer style="width: 90%;
            margin: 0 auto;
            font-size: 0;
            margin-bottom: 10%;
            padding-top: 6%;">
    <div class="tip" style="display: inline-block;margin-top: 0.3rem;min-width: 50%;">
        <img src="http://st.jcy.cc/img/m_more.png" style="width: 1.2rem;
            height: 1.2rem;
            vertical-align: bottom;">
        <p style="margin: 0;padding:0;display: inline-block;
            font-size: 1rem;
            line-height: 1.2rem;
            font-weight: bold;
            color: #1a1a1a;
            margin-left: 0.6rem;">了解更多<span style="margin-left: 0.6rem;"><a href="http://www.jcy.cc" style="color: #009DF5;">http://www.jcy.cc</a></span></p>
    </div>
    <div class="tip" style="display: inline-block;margin-top: 0.3rem;min-width: 50%;">
        <img src="http://st.jcy.cc/img/m_phone.png" style="width: 1.2rem;
            height: 1.2rem;
            vertical-align: bottom;">
        <p style="margin: 0;padding:0;display: inline-block;
            font-size: 1rem;
            line-height: 1.2rem;
            font-weight: bold;
            color: #1a1a1a;
            margin-left: 0.6rem;">全国咨询电话<span style="margin-left: 0.6rem;">400 838 2323</span></p>
    </div>
    <div class="copyright" style="margin-top: 5%;">
        <p class="chinese" style="margin: 0;padding:0;text-align: center;
            font-size: 0.9rem;
            line-height: 1.5rem;
            color: #969696;">
            <span>此邮件为系统邮件/请勿回复</span>
            <span>深圳市百纳十方科技发展有限公司版权所有</span>
        </p>
        <p class="english" style="margin: 0;padding:0;text-align: center;
            line-height: 1.5rem;
            color: #969696;
            font-size: 0.8rem;">To unsubscribe from this message click here.Or to unsubscribe from all future emails click here</p>
    </div>

</footer>

</div>
EOF;

        return $content;
    }




    public function userActivityNotice($params){

//        var_dump($params);
//        $member = model("ucenter/designer");
//        $member->set($params['uid'],array("register_progress"=>'6'));
        $this->initMail();
        $result = $this->sendmail(array('to'=>$params['to']), '邮箱激活通知', $this->getuserActivityNoticePage($params));
//        var_dump($result);
    }



    private function getDemandAuditPassedPage($params){

//        $yuyue = model('Ucenter/designer_yuyue');
//
//        $member = model('Ucenter/Member');
////        $realname = "肖喜进";
////        $realname = $yuyue->get_user_realname($designerId);
//        $tmp = $yuyue->get_order_info($yuyue_sn)['content'];
//
//        $orderinfo = (array)json_decode($tmp);
//        $cityName = $orderinfo['city'];
//        $cityAreaName = $orderinfo['city_area'];
//        $areaHomeName = $orderinfo['home'];
//        $houseSize = (int)$orderinfo['area'];
//        $price = $this->get_price($houseSize);
//        // 获取订单中的个性，设计风格标签
//        $style = $yuyue->get_order_style($yuyue_sn);
//        $style1 = current($style);$style2=next($style);$style3=next($style);
//        $interest = $yuyue->get_order_person($yuyue_sn);
//        $interest1 = current($interest);$interest2=next($interest);$interest3=next($interest);

        $content =<<<EOF
<div style="width: 800px;margin: 50px auto 0 auto;text-align: center;overflow: hidden;">
    <h1 style="padding: 0;font-size: 24px;line-height: 32px;margin: 50px;">
        <div style="content: '';display: inline-block;width: 22px;height: 22px;margin-bottom: 5px;background-repeat: no-repeat;background-size: 22px 22px;background: url('data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABYAAAAWCAYAAADEtGw7AAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAA3ppVFh0WE1MOmNvbS5hZG9iZS54bXAAAAAAADw/eHBhY2tldCBiZWdpbj0i77u/IiBpZD0iVzVNME1wQ2VoaUh6cmVTek5UY3prYzlkIj8+IDx4OnhtcG1ldGEgeG1sbnM6eD0iYWRvYmU6bnM6bWV0YS8iIHg6eG1wdGs9IkFkb2JlIFhNUCBDb3JlIDUuNS1jMDE0IDc5LjE1MTQ4MSwgMjAxMy8wMy8xMy0xMjowOToxNSAgICAgICAgIj4gPHJkZjpSREYgeG1sbnM6cmRmPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj4gPHJkZjpEZXNjcmlwdGlvbiByZGY6YWJvdXQ9IiIgeG1sbnM6eG1wTU09Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9tbS8iIHhtbG5zOnN0UmVmPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvc1R5cGUvUmVzb3VyY2VSZWYjIiB4bWxuczp4bXA9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC8iIHhtcE1NOk9yaWdpbmFsRG9jdW1lbnRJRD0ieG1wLmRpZDo3NDcwNjVjMy02MGI5LTRkNTQtODg5Yy04ZjA0Njg1MmRlOTMiIHhtcE1NOkRvY3VtZW50SUQ9InhtcC5kaWQ6MUY0QTczNTM1MkQ2MTFFNkEwOTI4OTBBQjU0RjJEOTQiIHhtcE1NOkluc3RhbmNlSUQ9InhtcC5paWQ6MUY0QTczNTI1MkQ2MTFFNkEwOTI4OTBBQjU0RjJEOTQiIHhtcDpDcmVhdG9yVG9vbD0iQWRvYmUgUGhvdG9zaG9wIENDIDIwMTUuNSAoTWFjaW50b3NoKSI+IDx4bXBNTTpEZXJpdmVkRnJvbSBzdFJlZjppbnN0YW5jZUlEPSJ4bXAuaWlkOjc0NzA2NWMzLTYwYjktNGQ1NC04ODljLThmMDQ2ODUyZGU5MyIgc3RSZWY6ZG9jdW1lbnRJRD0ieG1wLmRpZDo3NDcwNjVjMy02MGI5LTRkNTQtODg5Yy04ZjA0Njg1MmRlOTMiLz4gPC9yZGY6RGVzY3JpcHRpb24+IDwvcmRmOlJERj4gPC94OnhtcG1ldGE+IDw/eHBhY2tldCBlbmQ9InIiPz5/1VEoAAACcklEQVR42tRVz2sTYRB98+1md5vGppRaIXhQS6BYEYpVpK1gbh5EvbiHnvwjPOmlR4+evYgnwRa0ngSFCDapYBArFNGaiyRtFKlt0/zYJt3P2cWkm7i7wYOCe/tmZ943896bXZJS4m88Av/y4SGomsU5K4vrf1AjGq8xU8/isnOm7oTtLIb6CFekjWNugo3H2gWshIJmMNIgXGPwhHO2m3jQAVxZQkJTcMO2oXm4KqtF3CUT+36g9QxGFcKsLaG0YgxaVFuHzeeIK4ITPKCS8N2ysBAJAC1zp0QwO0AlCpF+LLjiyVVo0ShmORjzyLqWL+BeLIWSH+i3NGI6dwoJHQetrjxZx32awJYLXNvCGW7/SPs9YV0bwPy4ib0gXgd0TDOng57x84sFPDV/TSccBzD62XajAo2IjYc0Hgy6+ggsBSbaNYRKcQ/zpocyYS1jlG8bagX2JXI0g3KYC5IJnGZeDU8oczyFeveCjHkDusByT8+qHTXNTwXkfts85jPutRadx04vYPb2oMcFJT8tVEcAOrDXrpXBKTZ4pWRjo3s8z5a1gaWCmlsjsWMY2KBJNNwLrSXcYkk1n/oad/ase+scm8U13PS9EO60i8Y08oJdYAdM3EcKrlbTOOoNHj4UmO9YboC30JQ5xB27BXLK4wmh4WRH8SSqjmAhNXq1jqTg9jdDhSJEfUYOrWEW+gULlgtLYl9/9fmIv+nhmpIwpvCZyfkY0O2P4jbedsff63jnrH1AzZc7L7DmOm1uDuL2JUzJJsZ4zBHe8QorlNctvKQUdn0dkIZqGbjIXJ5gsGHp7ADwQR3GK0rCov/un/dTgAEAa0HeoKXuMvEAAAAASUVORK5CYII=');"></div>
        尊敬的{$params['realname']}先生
        <div style="content: '';display: inline-block;width: 22px;height: 22px;margin-bottom: 5px;background-repeat: no-repeat;background-size: 22px 22px;background: url('data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABYAAAAWCAYAAADEtGw7AAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAA3ppVFh0WE1MOmNvbS5hZG9iZS54bXAAAAAAADw/eHBhY2tldCBiZWdpbj0i77u/IiBpZD0iVzVNME1wQ2VoaUh6cmVTek5UY3prYzlkIj8+IDx4OnhtcG1ldGEgeG1sbnM6eD0iYWRvYmU6bnM6bWV0YS8iIHg6eG1wdGs9IkFkb2JlIFhNUCBDb3JlIDUuNS1jMDE0IDc5LjE1MTQ4MSwgMjAxMy8wMy8xMy0xMjowOToxNSAgICAgICAgIj4gPHJkZjpSREYgeG1sbnM6cmRmPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj4gPHJkZjpEZXNjcmlwdGlvbiByZGY6YWJvdXQ9IiIgeG1sbnM6eG1wTU09Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9tbS8iIHhtbG5zOnN0UmVmPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvc1R5cGUvUmVzb3VyY2VSZWYjIiB4bWxuczp4bXA9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC8iIHhtcE1NOk9yaWdpbmFsRG9jdW1lbnRJRD0ieG1wLmRpZDplZGRiY2YxYS1mMGY5LTQzN2MtYjU3Ny0yMmQxZTE3OWM2NzgiIHhtcE1NOkRvY3VtZW50SUQ9InhtcC5kaWQ6MUY0QTczNEY1MkQ2MTFFNkEwOTI4OTBBQjU0RjJEOTQiIHhtcE1NOkluc3RhbmNlSUQ9InhtcC5paWQ6MUY0QTczNEU1MkQ2MTFFNkEwOTI4OTBBQjU0RjJEOTQiIHhtcDpDcmVhdG9yVG9vbD0iQWRvYmUgUGhvdG9zaG9wIENDIDIwMTUuNSAoTWFjaW50b3NoKSI+IDx4bXBNTTpEZXJpdmVkRnJvbSBzdFJlZjppbnN0YW5jZUlEPSJ4bXAuaWlkOmVkZGJjZjFhLWYwZjktNDM3Yy1iNTc3LTIyZDFlMTc5YzY3OCIgc3RSZWY6ZG9jdW1lbnRJRD0ieG1wLmRpZDplZGRiY2YxYS1mMGY5LTQzN2MtYjU3Ny0yMmQxZTE3OWM2NzgiLz4gPC9yZGY6RGVzY3JpcHRpb24+IDwvcmRmOlJERj4gPC94OnhtcG1ldGE+IDw/eHBhY2tldCBlbmQ9InIiPz7J9zijAAACdklEQVR42tRVTWsTURQ9782bTicSNSoaQ4sIBhddVDEWW8Uqggt3oha7dOm/6Ka/QURddhNwJbiQYhYmtoIIilI01CB+pxVTpCYzk5nnnUk6vDgziZsufDCZ4ebdM+fec94dJqXEdiyObVrM/6mVMDxiYFoyHJUe0lTED3plzRjCY1aAE5coy0jbHBcYw2HpwmQa6szF2/lFVObm4DEC4c4SbtB9NJIMfF/9jHtjM7B74lUY1hpuMondMT2oGpNY4PYysYwB7ZZzID+K43/H2+soxIL6y0O+tYw85xL7+zbLw4lIaEAO81DgtsTvfpuoHXsi1TL86gsskeEpA++oZqvPPiGfI9UT0PCactxEMgy7OKm+QT0ukrqNRGi9456Q0QS+oY37xHwzycNhgixBNE1kNTcQ5RhdR4JNHI4+iflYZm8w1GrgIAH5Fj1D5LLd9tVFyOI8cQA++Ze9hFPk52C5EhuJvRwLbPjBf7YquBTGqfrIyas+hEGqZhUhGoNOGWmwj26hDkQqCjyyExNEVijWWRkEbFs43fMiFys9wPIpTFJ8KhSBo6mbeDWILYk4rtizbk6jFjIrkXiOxHUSwVSO3oukWeGvLw+QsizMMmWYcQ3PAktugU4ZuEyghxQv/hQClUSmNLgcIyCyVxHtY6WJlx1tniDjaLhGG3LKyWqJIdwltutxoJtl5HQe5GQUIg1jB+6w8Y63hSNwhVTMKeW7ro6ingAqi9DaDLM0L9KqFsLBwhZoEHNdPKI/ZPetX2l23B4+ifeJ3p0ha3tYVCbgqmC4xc5iLbLZqeAizeRzRWLzr18IOhBX7TIKyYPof/vm/RFgAP6Z4wdMMi9xAAAAAElFTkSuQmCC');"></div>
    </h1>
</div>

<section style="padding-bottom: 50px;width: 800px;margin: 0 auto;text-align: center;">
    <p style="font-size: 12px;line-height: 20px;color: #666;padding: 0;margin: 0;">感谢选择家创易，我们正在为你匹配最合适的设计师，请耐心等待。</p>
    <!-- 订单信息 begin -->
    <div style="position: relative;width: 402px;border: 1px solid #eee;-webkit-box-sizing: border-box;box-sizing: border-box;margin: 20px auto;box-shadow: 0 10px 10px #ccc;display: -webkit-flex;display: -moz-box;display: -ms-flexbox;display: flex;display: -webkit-box;-webkit-box-pack: center;-moz-box-pack: center;-ms-box-pack: center;box-pack: center;-webkit-box-align: center;-moz-box-align: center;-ms-box-align: center;box-align: center;">
        <div style="padding: 10px;-webkit-box-sizing: border-box;box-sizing: border-box;width: 400px;background: #fff;">
            <div style="height: 26px;line-height: 20px;overflow: hidden;">
                <span style="float: left;font-size: 12px;color: #222;font-weight: bold;margin-left: -3px;max-width: 32%;height: 1rem;overflow: hidden;">
                    <i style="float: left;height: 17px;padding-right: 2px;overflow: hidden;font-style: normal;">
                        <img src="http://st.jcy.cc/img/location.png" style="height: 100%;margin: 0;padding: 0;"/>
                    </i>
                    {$params['projectAddr']}
                </span>
                <span style="float: left;height: 10px;width: 1px;border-left: 1px dotted #ccc;margin: 5px 8px 0 8px;"></span>
                <span style="display: block;float: left;font-size: 12px;color: #222;font-weight: bold;max-width: 38%;height: 1rem;overflow: hidden;">{$params['homeName']}</span>
            </div>
            <div style="font-family: 'Tahoma';font-size: 24px;color: rgba(0,0,0,0.87);line-height: 32px;overflow: hidden;text-align: right">
                {$params['homeArea']}m²&emsp;{$params['project_type']}
            </div>
            <div style="margin-top: 25px;overflow: hidden;">
                <p style="overflow: hidden;font-size: 12px;color: #666;line-height: 16px;margin: 0;padding: 0;text-align: left;">
                    下午茶时间与地点：
                </p>
                <p style="position:relative;overflow: hidden;font-size: 12px;color: #666;height:24px;line-height: 24px;margin: 0;padding: 0;text-align: left;font-weight: bold;">
                    <span style="position: absolute;left: 0;top:0;">{$params['interview_time']}</span>
                    <span style="position: absolute;right: 0;top:0;">{$params['interview_add']}</span>
                </p>
            </div>
        </div>
        <div style="content: '';display: table;clear: both;"></div>
    </div>
    <!-- 订单信息 end -->
    <ul class="progress-list" style="font-size: 12px;color: #aaa;margin-bottom: 50px;margin-top: 120px;list-style: none;padding: 0;">
        <li style="color: #666;position: relative;float: left;list-style: none;padding: 0;margin: 0;">
            预约<span style="color: #009DF5;">&emsp;&radic;&emsp;</span>
        </li>
        <li style="color: #009DF5;position: relative;float: left;list-style: none;padding: 0;margin: 0;">
            <span style="position: absolute;top: -40px;left: 5px;background: #009DF5;border-radius: 6px;color: #fff;font-size: 12px;padding: 6px 0;text-align: center;width: 88px;">当前进度
                <span style="content: '';position: absolute;top: 24px;left: 38px;width: 0;height: 0;border-style: solid;border-width: 6px;border-color: transparent;border-top-color: #009DF5;"></span>
            </span>
             匹配设计师<span style="color: #009DF5;">&emsp;&rarr;&emsp;</span>
        </li>

        <li style="position: relative;float: left;list-style: none;padding: 0;margin: 0;">
            筛选约见<span style="color: #aaa;">&emsp;&rarr;&emsp;</span>
        </li>

        <li style="position: relative;float: left;list-style: none;padding: 0;margin: 0;">
            约谈设计<span style="color: #aaa;">&emsp;&rarr;&emsp;</span>
        </li>

        <li style="position: relative;float: left;list-style: none;padding: 0;margin: 0;">
            签订设计合同<span style="color: #aaa;">&emsp;&rarr;&emsp;</span>
        </li>

        <li style="position: relative;float: left;list-style: none;padding: 0;margin: 0;">
             定制设计<span style="color: #aaa;">&emsp;&rarr;&emsp;</span>
        </li>

        <li style="position: relative;float: left;list-style: none;padding: 0;margin: 0;">
            装修跟进<span style="color: #aaa;">&emsp;&rarr;&emsp;</span>
        </li>

        <li style="position: relative;float: left;list-style: none;padding: 0;margin: 0;">
            完工/点评
        </li>

        <div style="content: '';display: table;clear: both;"></div>
    </ul>
    <!--<p style="margin: 32px 0 0 0;font-size: 12px;line-height: 20px;color: #666;padding: 0;">你家的设计师，不必远，不必贵，不必有名，适合就好。</p>-->
    <div style="max-width: 800px;margin-top: 100px;">
        <img src="http://st.jcy.cc/img/pic_jcy.png" style="display: block;width: 100%;margin: 0;padding: 0;">
    </div>
</section>

<footer style="border-top: 1px solid #eee;padding-top: 50px;width: 800px;margin: 0 auto;text-align: center;">
    <div style="margin-top: 0;box-sizing: border-box;font-size: 12px;height: 100px;background: #f6f6f6;padding-top: 30px;">
        <p style="color: #aaa;font-size: 12px;line-height: 20px;margin: 0;padding: 0;">全国免费服务热线 400-830-2323</p>
        <small style="display: block;color: #aaa;font-style: normal;text-align: center;">Copyright © 2015-2016 All Rights Reserved. 粤ICP备15096730号</small>
    </div>
</footer>
EOF;
        return $content;

    }


    /**
     *  通知业主已发布需求=>step 1.
     */
    public function DemandAuditPassed($params){


        $this->initMail();
        $this->sendmail(array('to'=>$params['to']), '需求成功通过审核', $this->getDemandAuditPassedPage($params));

    }

    public function getReleaseDemandPage($params){
        $member_info = model("ucenter/member")->get($params['uid']);
        $content =<<<EOF
有业主{$member_info['realname']} 发布了新需求，请登录系统后台查看。
EOF;
        return $content;
    }

    /**
     *  通知业主已发布需求=>step 1.
     */
    public function ReleaseDemand($params){


        $this->initMail();
        $this->sendmail(array('to'=>"market@jcy.cc"), '业主发布新需求', $this->getReleaseDemandPage($params));

    }


}
