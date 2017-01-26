<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/7/22
 * Time: 11:27
 */
namespace Module\Tools\Controllers;
use \Module\Tools\Controller as Controller;

class DesignerMail extends Controller
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

    public function getDesignerCaseAccessEmail($params){
        $content =<<<EOF

<div class="mail-box" style="max-width: 640px;
            margin: 0 auto;
            font-size: 16px;
            color: #4C4C4C;
            font-family: 'Helvetica Neue,Helvetica,Arial,sans-serif';">
<header style="border-bottom:1px solid #aaa;">
    <div class="head-box" style="width: 90%;margin: 3% auto;font-weight: bold;">
        <h5 style="margin: 0; padding:0;margin-bottom: 3%;font-size: 1.2rem;line-height: 1.5rem;">新消息/设计作品已成功通过审核</h5>
        <p style="margin: 0;padding:0;display: inline-block;font-size: 0.9rem;line-height: 1.2rem;">家创易设计师服务平台</p>
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
            line-height: 2.0rem;">{$params['realname']}设计师：</h6>
    <p style="margin: 0;padding:0;font-size: 1.2rem;
            line-height: 2.0rem;">你发布的作品---{$params['casename']}已成功通过审核，点击以下按钮发布更多作品。</p>
            
            
     <a  href="http://www.jcy.cc/ucenter/designer/verify-uploadcase.html" style=" display: block;
                width:90%;
                height: 3.9rem;
                margin: 10% auto;
                text-align: center;
                text-decoration: none;
                font-size: 1.4rem;
                font-weight: bold;
                color: #ddd !important;
                line-height: 3.9rem;
                background-color:#222 !important;
                border-radius: 5px;">发布作品</a>
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

    public function designerCaseAccessEmail($params){

        $this->initMail();
        $this->sendmail(array('to'=>$params['to']), '案例审核通过通知', $this->getDesignerCaseAccessEmail($params));
    }
//    public function designerSignUpAccessEmail($params){
//        $this->initMail();
//        $this->sendmail(array('to'=>$params['to']), '个人信息审核通过', $this->getDesignerSignUpAccessEmail($params['realname']));
//
//    }


    public function getDesignerCaseRejectEmail($params){
        $content =<<<EOF

<div class="mail-box" style="max-width: 640px;
            margin: 0 auto;
            font-size: 16px;
            color: #4C4C4C;
            font-family: 'Helvetica Neue,Helvetica,Arial,sans-serif';">
<header style="border-bottom:1px solid #aaa;">
    <div class="head-box" style="width: 90%;margin: 3% auto;font-weight: bold;">
        <h5 style="margin: 0; padding:0;margin-bottom: 3%;font-size: 1.2rem;line-height: 1.5rem;">新消息/设计作品未能通过审核</h5>
        <p style="margin: 0;padding:0;display: inline-block;font-size: 0.9rem;line-height: 1.2rem;">家创易设计师服务平台</p>
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
            line-height: 2.0rem;">{$params['realname']}设计师：</h6>
    <p style="margin: 0;padding:0;font-size: 1.2rem;
            line-height: 2.0rem;">你发布的作品---{$params['casename']}未能通过审核,原因是：（照片模糊不清晰或作品不符合平台定位），如需拍摄可以关注公众号：微致品牌工坊。点击一下按钮重新发布作品</p>
            
    
    
    <a class="btn" href="http://www.jcy.cc/ucenter/designer/verify-uploadcase.html" style=" display: block;
            width:90%;
            height: 10%;
            margin: 10% auto;
            text-align: center;
            text-decoration: none;
            font-size: 1.4rem;
            font-weight: bold;
            color: #ddd;
            line-height: 3.9rem;
            background-color:#222;
            border-radius: 5px;">发布作品</a>
</section>

<footer style="width: 90%;
            margin: 0 auto;
            font-size: 0;
            margin-bottom: 10%;
            padding-top: 6%;">
    <div class="tip" style="display: inline-block;margin-top: 0.3rem;min-width: 50%;">
        <img src="img/m_more.png" style="width: 1.2rem;
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



    public function designerCaseRejectEmail($params){

        $this->initMail();
        $this->sendmail(array('to'=>$params['to']), '案例审核未通过通知', $this->getDesignerCaseRejectEmail($params));
    }


    public function getDesignerGrabOrderSuccess($params){

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
            <h5 style="margin: 0; padding:0;margin-bottom: 3%;font-size: 1.2rem;line-height: 1.5rem;">新消息/成功抢单通知</h5>
            <p style="margin: 0;padding:0;display: inline-block;font-size: 0.9rem;line-height: 1.2rem;">家创易设计师服务平台</p>
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
                line-height: 2.0rem;">尊敬的{$params['realname']}设计师：</h6>
        <p style="margin: 0;padding:0; font-size: 1.2rem;
                line-height: 2.0rem;">你已成功抢单，客户将会选择希望见面的设计师，请耐心等待。</p>
                
        <p style="margin: 0;padding:0; font-size: 1.2rem;line-height: 2.0rem;">抢单费：{$params['price']}元</p>
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
                min-width: 50%;">抢单排名：第{$params['sort']}位</p>
               
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

    /**
     * 设计师 抢单成功
     */
    public function designerGrabOrderSuccess($params){

        $this->initMail();
        $this->sendmail(array('to'=>$params['to']), '成功抢单通知', $this->getDesignerGrabOrderSuccess($params));
    }


    public function getrefundPricePage($params){
        $content =<<<EOF
        
        
我是个天才
EOF;


        return $content;
    }
    /**
     * 设计师 退款成功
     */
    public function refundPrice($params){

        $this->initMail();
        $this->sendmail(array('to'=>$params['to']), '退款通知', $this->getrefundPricePage($params));
    }


}
