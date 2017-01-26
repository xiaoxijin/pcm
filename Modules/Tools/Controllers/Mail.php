<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/7/22
 * Time: 11:27
 */
namespace Module\Tools\Controllers;
use \Module\Tools\Controller as Controller;

class Mail extends Controller
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
     *  获取订单数据相关函数                                                        *
     ********************************************************************************/
    /**
     *  设计师,业主 获取订单信息接口
     *  $userId enum($designerId, $ownerId)
     */
    private function designer_get_order_info($userId, $orderNo){

    }

    /**
     *  获取下午茶信息接口
     *  $orderNo 订单no
     */
    private function get_order_tea($yuyue_id){



    }

    /**
     * 获得当前订单和设计师的匹配情况
     */
    private function designer_label_match($designerId, $yuyue_id){
//        $yuyue = model('Orders/designer_yuyue')->matchLabel($designerId, $yuyue_id);
        $yuyue = model('Orders/designer_yuyue')->get_user_realname($designerId);
    }

    /**
     *  计算出当前订单面积的价格
     */
    private function get_price($areaSize){
        //计算出订单价格
        if($areaSize<=150){
            $price=100;
        }elseif($areaSize>=151 && $areaSize<=300){
            $price=150;
        }elseif($areaSize>=301 && $areaSize<=500){
            $price=200;
        }elseif($areaSize>=501){
            $price=300;
        }
        return $price;
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


    /**
     * 通知设计师抢单
     */
    private function designer_notice_grab($designerId, $yuyue_sn){
        $yuyue = model('Orders/Designer_yuyue');

        $realname = $yuyue->get_user_realname($designerId);

        $yuyueOrderInfo = $yuyue->get($yuyue_sn);
        $tmp = $yuyueOrderInfo['content'];

        $orderinfo = (array)json_decode($tmp);
        $cityName = $orderinfo['city'];
        $cityAreaName = $orderinfo['city_area'];
        $areaHomeName = $orderinfo['home'];
        $houseSize = (int)$orderinfo['area'];
        $price = $this->get_price($houseSize);
        // 获取订单中的个性，设计风格标签
        $style = $yuyue->get_order_style($yuyue_sn);
        $style1 = current($style);$style2=next($style);$style3=next($style);
        $interest = $yuyue->get_order_person($yuyue_sn);

        $interest1 = current($interest);$interest2=next($interest);$interest3=next($interest);$interest4=next($interest);


//        var_dump($yuyue_sn);

//        var_dump($yuyueOrderInfo);
        $content =<<<EOF
        <div style="width: 800px;margin: 50px auto 0 auto;text-align: center;overflow: hidden;">
            <h1 style="padding: 0;font-size: 24px;line-height: 32px;margin: 50px;">
                <div style="content: '';display: inline-block;width: 22px;height: 22px;margin-bottom: 5px;background-repeat: no-repeat;background-size: 22px 22px;background: url('data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABYAAAAWCAYAAADEtGw7AAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAA3ppVFh0WE1MOmNvbS5hZG9iZS54bXAAAAAAADw/eHBhY2tldCBiZWdpbj0i77u/IiBpZD0iVzVNME1wQ2VoaUh6cmVTek5UY3prYzlkIj8+IDx4OnhtcG1ldGEgeG1sbnM6eD0iYWRvYmU6bnM6bWV0YS8iIHg6eG1wdGs9IkFkb2JlIFhNUCBDb3JlIDUuNS1jMDE0IDc5LjE1MTQ4MSwgMjAxMy8wMy8xMy0xMjowOToxNSAgICAgICAgIj4gPHJkZjpSREYgeG1sbnM6cmRmPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj4gPHJkZjpEZXNjcmlwdGlvbiByZGY6YWJvdXQ9IiIgeG1sbnM6eG1wTU09Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9tbS8iIHhtbG5zOnN0UmVmPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvc1R5cGUvUmVzb3VyY2VSZWYjIiB4bWxuczp4bXA9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC8iIHhtcE1NOk9yaWdpbmFsRG9jdW1lbnRJRD0ieG1wLmRpZDo3NDcwNjVjMy02MGI5LTRkNTQtODg5Yy04ZjA0Njg1MmRlOTMiIHhtcE1NOkRvY3VtZW50SUQ9InhtcC5kaWQ6MUY0QTczNTM1MkQ2MTFFNkEwOTI4OTBBQjU0RjJEOTQiIHhtcE1NOkluc3RhbmNlSUQ9InhtcC5paWQ6MUY0QTczNTI1MkQ2MTFFNkEwOTI4OTBBQjU0RjJEOTQiIHhtcDpDcmVhdG9yVG9vbD0iQWRvYmUgUGhvdG9zaG9wIENDIDIwMTUuNSAoTWFjaW50b3NoKSI+IDx4bXBNTTpEZXJpdmVkRnJvbSBzdFJlZjppbnN0YW5jZUlEPSJ4bXAuaWlkOjc0NzA2NWMzLTYwYjktNGQ1NC04ODljLThmMDQ2ODUyZGU5MyIgc3RSZWY6ZG9jdW1lbnRJRD0ieG1wLmRpZDo3NDcwNjVjMy02MGI5LTRkNTQtODg5Yy04ZjA0Njg1MmRlOTMiLz4gPC9yZGY6RGVzY3JpcHRpb24+IDwvcmRmOlJERj4gPC94OnhtcG1ldGE+IDw/eHBhY2tldCBlbmQ9InIiPz5/1VEoAAACcklEQVR42tRVz2sTYRB98+1md5vGppRaIXhQS6BYEYpVpK1gbh5EvbiHnvwjPOmlR4+evYgnwRa0ngSFCDapYBArFNGaiyRtFKlt0/zYJt3P2cWkm7i7wYOCe/tmZ943896bXZJS4m88Av/y4SGomsU5K4vrf1AjGq8xU8/isnOm7oTtLIb6CFekjWNugo3H2gWshIJmMNIgXGPwhHO2m3jQAVxZQkJTcMO2oXm4KqtF3CUT+36g9QxGFcKsLaG0YgxaVFuHzeeIK4ITPKCS8N2ysBAJAC1zp0QwO0AlCpF+LLjiyVVo0ShmORjzyLqWL+BeLIWSH+i3NGI6dwoJHQetrjxZx32awJYLXNvCGW7/SPs9YV0bwPy4ib0gXgd0TDOng57x84sFPDV/TSccBzD62XajAo2IjYc0Hgy6+ggsBSbaNYRKcQ/zpocyYS1jlG8bagX2JXI0g3KYC5IJnGZeDU8oczyFeveCjHkDusByT8+qHTXNTwXkfts85jPutRadx04vYPb2oMcFJT8tVEcAOrDXrpXBKTZ4pWRjo3s8z5a1gaWCmlsjsWMY2KBJNNwLrSXcYkk1n/oad/ase+scm8U13PS9EO60i8Y08oJdYAdM3EcKrlbTOOoNHj4UmO9YboC30JQ5xB27BXLK4wmh4WRH8SSqjmAhNXq1jqTg9jdDhSJEfUYOrWEW+gULlgtLYl9/9fmIv+nhmpIwpvCZyfkY0O2P4jbedsff63jnrH1AzZc7L7DmOm1uDuL2JUzJJsZ4zBHe8QorlNctvKQUdn0dkIZqGbjIXJ5gsGHp7ADwQR3GK0rCov/un/dTgAEAa0HeoKXuMvEAAAAASUVORK5CYII=');"></div>
                尊敬的{$realname}设计师
                <div style="content: '';display: inline-block;width: 22px;height: 22px;margin-bottom: 5px;background-repeat: no-repeat;background-size: 22px 22px;background: url('data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABYAAAAWCAYAAADEtGw7AAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAA3ppVFh0WE1MOmNvbS5hZG9iZS54bXAAAAAAADw/eHBhY2tldCBiZWdpbj0i77u/IiBpZD0iVzVNME1wQ2VoaUh6cmVTek5UY3prYzlkIj8+IDx4OnhtcG1ldGEgeG1sbnM6eD0iYWRvYmU6bnM6bWV0YS8iIHg6eG1wdGs9IkFkb2JlIFhNUCBDb3JlIDUuNS1jMDE0IDc5LjE1MTQ4MSwgMjAxMy8wMy8xMy0xMjowOToxNSAgICAgICAgIj4gPHJkZjpSREYgeG1sbnM6cmRmPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj4gPHJkZjpEZXNjcmlwdGlvbiByZGY6YWJvdXQ9IiIgeG1sbnM6eG1wTU09Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9tbS8iIHhtbG5zOnN0UmVmPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvc1R5cGUvUmVzb3VyY2VSZWYjIiB4bWxuczp4bXA9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC8iIHhtcE1NOk9yaWdpbmFsRG9jdW1lbnRJRD0ieG1wLmRpZDplZGRiY2YxYS1mMGY5LTQzN2MtYjU3Ny0yMmQxZTE3OWM2NzgiIHhtcE1NOkRvY3VtZW50SUQ9InhtcC5kaWQ6MUY0QTczNEY1MkQ2MTFFNkEwOTI4OTBBQjU0RjJEOTQiIHhtcE1NOkluc3RhbmNlSUQ9InhtcC5paWQ6MUY0QTczNEU1MkQ2MTFFNkEwOTI4OTBBQjU0RjJEOTQiIHhtcDpDcmVhdG9yVG9vbD0iQWRvYmUgUGhvdG9zaG9wIENDIDIwMTUuNSAoTWFjaW50b3NoKSI+IDx4bXBNTTpEZXJpdmVkRnJvbSBzdFJlZjppbnN0YW5jZUlEPSJ4bXAuaWlkOmVkZGJjZjFhLWYwZjktNDM3Yy1iNTc3LTIyZDFlMTc5YzY3OCIgc3RSZWY6ZG9jdW1lbnRJRD0ieG1wLmRpZDplZGRiY2YxYS1mMGY5LTQzN2MtYjU3Ny0yMmQxZTE3OWM2NzgiLz4gPC9yZGY6RGVzY3JpcHRpb24+IDwvcmRmOlJERj4gPC94OnhtcG1ldGE+IDw/eHBhY2tldCBlbmQ9InIiPz7J9zijAAACdklEQVR42tRVTWsTURQ9782bTicSNSoaQ4sIBhddVDEWW8Uqggt3oha7dOm/6Ka/QURddhNwJbiQYhYmtoIIilI01CB+pxVTpCYzk5nnnUk6vDgziZsufDCZ4ebdM+fec94dJqXEdiyObVrM/6mVMDxiYFoyHJUe0lTED3plzRjCY1aAE5coy0jbHBcYw2HpwmQa6szF2/lFVObm4DEC4c4SbtB9NJIMfF/9jHtjM7B74lUY1hpuMondMT2oGpNY4PYysYwB7ZZzID+K43/H2+soxIL6y0O+tYw85xL7+zbLw4lIaEAO81DgtsTvfpuoHXsi1TL86gsskeEpA++oZqvPPiGfI9UT0PCactxEMgy7OKm+QT0ukrqNRGi9456Q0QS+oY37xHwzycNhgixBNE1kNTcQ5RhdR4JNHI4+iflYZm8w1GrgIAH5Fj1D5LLd9tVFyOI8cQA++Ze9hFPk52C5EhuJvRwLbPjBf7YquBTGqfrIyas+hEGqZhUhGoNOGWmwj26hDkQqCjyyExNEVijWWRkEbFs43fMiFys9wPIpTFJ8KhSBo6mbeDWILYk4rtizbk6jFjIrkXiOxHUSwVSO3oukWeGvLw+QsizMMmWYcQ3PAktugU4ZuEyghxQv/hQClUSmNLgcIyCyVxHtY6WJlx1tniDjaLhGG3LKyWqJIdwltutxoJtl5HQe5GQUIg1jB+6w8Y63hSNwhVTMKeW7ro6ingAqi9DaDLM0L9KqFsLBwhZoEHNdPKI/ZPetX2l23B4+ifeJ3p0ha3tYVCbgqmC4xc5iLbLZqeAizeRzRWLzr18IOhBX7TIKyYPof/vm/RFgAP6Z4wdMMi9xAAAAAElFTkSuQmCC');"></div>
            </h1>
        </div>
        <section style="width: 800px;margin: 0 auto;text-align: center;padding-bottom: 100px;">
            <p style="font-size: 12px;line-height: 16px;color: #666;margin: 0;padding: 0;">一位客户发布了设计需求, 与您匹配度极高</p>


            <p style="font-size: 12px;line-height: 16px;color: #666;margin: 20px 0px 6px 0px;padding: 0;">设计下午茶时间：<span style="color: #222;">{$yuyueOrderInfo["interview_time"]}</span></p>
            <p style="font-size: 12px;line-height: 16px;color: #666;margin: 0;padding: 0;">地点：<span style="color: #222;">{$yuyueOrderInfo["interview_add"]}</span></p>

            <div style="position: relative;width: 430px;border: 1px solid #eee;height: 138px;-webkit-box-sizing: border-box;box-sizing: border-box;margin: 20px auto;box-shadow: 0 10px 10px #ccc;display: -webkit-flex;display: -moz-box;display: -ms-flexbox;display: flex;display: -webkit-box;-webkit-box-pack: center;-moz-box-pack: center;-ms-box-pack: center;box-pack: center;-webkit-box-align: center;-moz-box-align: center;-ms-box-align: center;box-align: center;">
                <div style="padding: 10px;-webkit-box-sizing: border-box;box-sizing: border-box;width: 400px;background: #fff;">
                    <div style="height: 26px;line-height: 20px;overflow: hidden;">
                        <span style="float: left;font-size: 12px;color: #222;font-weight: bold;margin-left: -3px;max-width: 32%;height: 1rem;overflow: hidden;">
                            <i style="float: left;height: 17px;padding-right: 2px;overflow: hidden;font-style: normal;">
                                <img src="http://st.jcy.cc/location.png" style="height: 100%;margin: 0;padding: 0;"/>
                            </i>
                            $cityName-$cityAreaName
                        </span>
                        <span style="float: left;height: 10px;width: 1px;border-left: 1px dotted #ccc;margin: 5px 8px 0 8px;"></span>
                        <span style="display: block;float: left;font-size: 12px;color: #222;font-weight: bold;max-width: 38%;height: 1rem;overflow: hidden;">$areaHomeName</span>
                    </div>
                    <div style="height: 24px;line-height: 24px;overflow: hidden;">
                        <span style="float: left;font-size: 20px;color: #222;">{$houseSize}m²</span>
                        <span class="money" style="float: right;font-size: 20px;color: #222;">
                            <i style="font-size: 14px;line-height: 1;font-style: normal;">抢单费 ￥</i><i style="font-style: normal;font-size: 20px;color: #222;line-height: 24px;">$price</i>
                        </span>
                    </div>
                    <div style="margin-top: 25px;overflow: hidden;">
                        <p style="float: left;max-width: 40%;overflow: hidden;font-size: 12px;color: #aaa;line-height: 16px;margin: 0;padding: 0;">
                            <span style="display: inline-block;margin-right: 5px;">
                                <i style="font-style: normal;">#</i>$style1
                            </span>
                            <span style="display: inline-block;margin-right: 5px;">
                                <i style="font-style: normal;">#</i>$style2
                            </span>
                        </p>
                        <p style="float: right;max-width: 60%;overflow: hidden;font-size: 12px;color: #aaa;line-height: 16px;margin: 0;">
                            <span style="margin-right: 0;display: inline-block;">
                                <i style="font-style: normal;">#</i>$interest1
                            </span>
                            <span style="margin-right: 0;display: inline-block;">
                                <i style="font-style: normal;">#</i>$interest2
                            </span>
                            <span style="margin-right: 0;display: inline-block;">
                                <i style="font-style: normal;">#</i>$interest3
                            </span>
                            <span style="margin-right: 0;display: inline-block;">
                                <i style="font-style: normal;">#</i>$interest4
                            </span>
                        </p>
                    </div>
                </div>
            </div>
            <p style="margin: 0;padding: 0;font-size: 12px;color: #666;">请用手机浏览器打开链接：<a style="color:#009DF5;" href="http://ds.jcy.cc">http://ds.jcy.cc</a> 抢单。</p>
        </section>

        <footer style="border-top: 1px solid #eee;padding-top: 50px;width: 800px;margin: 0 auto;text-align: center;">
            <img src="http://st.jcy.cc/getorder.png" style="display: block;margin: 0 auto;padding: 0;">
            
            <p style="margin: 0;padding: 20px;font-size: 12px;color: #666;">如需了解抢单规则，请用手机浏览器打开：<a style="color:#009DF5;" href="http://ds.jcy.cc/help">http://ds.jcy.cc/help</a></p>


            <div class="bt" style="margin-top: 50px;-webkit-box-sizing: border-box;box-sizing: border-box;font-size: 12px;height: 100px;background: #f6f6f6;padding-top: 30px;">
                <p style="font-size: 12px;line-height: 20px;padding: 0;margin: 0;color: #aaa;">全国免费服务热线 400-830-2323</p>
                <small style="display: block;color: #aaa;font-style: normal;">Copyright © 2015-2016 All Rights Reserved. 粤ICP备15096730号</small>
            </div>
            

        </footer>
EOF;
        return $content;
    }

    /**
     * 设计师抢单成功后预约业主
     */
    private function designer_subcribe_owner(){
        $content =<<<EOF
        <header style="width: 800px;margin: 50px auto 0 auto;text-align: center;overflow: hidden;">
            <h1 style="padding: 0;font-size: 24px;line-height: 32px;margin: 50px;">
                <div style="content: '';display: inline-block;width: 22px;height: 22px;margin-bottom: 5px;background-repeat: no-repeat;background-size: 22px 22px;background: url('data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABYAAAAWCAYAAADEtGw7AAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAA3ppVFh0WE1MOmNvbS5hZG9iZS54bXAAAAAAADw/eHBhY2tldCBiZWdpbj0i77u/IiBpZD0iVzVNME1wQ2VoaUh6cmVTek5UY3prYzlkIj8+IDx4OnhtcG1ldGEgeG1sbnM6eD0iYWRvYmU6bnM6bWV0YS8iIHg6eG1wdGs9IkFkb2JlIFhNUCBDb3JlIDUuNS1jMDE0IDc5LjE1MTQ4MSwgMjAxMy8wMy8xMy0xMjowOToxNSAgICAgICAgIj4gPHJkZjpSREYgeG1sbnM6cmRmPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj4gPHJkZjpEZXNjcmlwdGlvbiByZGY6YWJvdXQ9IiIgeG1sbnM6eG1wTU09Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9tbS8iIHhtbG5zOnN0UmVmPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvc1R5cGUvUmVzb3VyY2VSZWYjIiB4bWxuczp4bXA9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC8iIHhtcE1NOk9yaWdpbmFsRG9jdW1lbnRJRD0ieG1wLmRpZDo3NDcwNjVjMy02MGI5LTRkNTQtODg5Yy04ZjA0Njg1MmRlOTMiIHhtcE1NOkRvY3VtZW50SUQ9InhtcC5kaWQ6MUY0QTczNTM1MkQ2MTFFNkEwOTI4OTBBQjU0RjJEOTQiIHhtcE1NOkluc3RhbmNlSUQ9InhtcC5paWQ6MUY0QTczNTI1MkQ2MTFFNkEwOTI4OTBBQjU0RjJEOTQiIHhtcDpDcmVhdG9yVG9vbD0iQWRvYmUgUGhvdG9zaG9wIENDIDIwMTUuNSAoTWFjaW50b3NoKSI+IDx4bXBNTTpEZXJpdmVkRnJvbSBzdFJlZjppbnN0YW5jZUlEPSJ4bXAuaWlkOjc0NzA2NWMzLTYwYjktNGQ1NC04ODljLThmMDQ2ODUyZGU5MyIgc3RSZWY6ZG9jdW1lbnRJRD0ieG1wLmRpZDo3NDcwNjVjMy02MGI5LTRkNTQtODg5Yy04ZjA0Njg1MmRlOTMiLz4gPC9yZGY6RGVzY3JpcHRpb24+IDwvcmRmOlJERj4gPC94OnhtcG1ldGE+IDw/eHBhY2tldCBlbmQ9InIiPz5/1VEoAAACcklEQVR42tRVz2sTYRB98+1md5vGppRaIXhQS6BYEYpVpK1gbh5EvbiHnvwjPOmlR4+evYgnwRa0ngSFCDapYBArFNGaiyRtFKlt0/zYJt3P2cWkm7i7wYOCe/tmZ943896bXZJS4m88Av/y4SGomsU5K4vrf1AjGq8xU8/isnOm7oTtLIb6CFekjWNugo3H2gWshIJmMNIgXGPwhHO2m3jQAVxZQkJTcMO2oXm4KqtF3CUT+36g9QxGFcKsLaG0YgxaVFuHzeeIK4ITPKCS8N2ysBAJAC1zp0QwO0AlCpF+LLjiyVVo0ShmORjzyLqWL+BeLIWSH+i3NGI6dwoJHQetrjxZx32awJYLXNvCGW7/SPs9YV0bwPy4ib0gXgd0TDOng57x84sFPDV/TSccBzD62XajAo2IjYc0Hgy6+ggsBSbaNYRKcQ/zpocyYS1jlG8bagX2JXI0g3KYC5IJnGZeDU8oczyFeveCjHkDusByT8+qHTXNTwXkfts85jPutRadx04vYPb2oMcFJT8tVEcAOrDXrpXBKTZ4pWRjo3s8z5a1gaWCmlsjsWMY2KBJNNwLrSXcYkk1n/oad/ase+scm8U13PS9EO60i8Y08oJdYAdM3EcKrlbTOOoNHj4UmO9YboC30JQ5xB27BXLK4wmh4WRH8SSqjmAhNXq1jqTg9jdDhSJEfUYOrWEW+gULlgtLYl9/9fmIv+nhmpIwpvCZyfkY0O2P4jbedsff63jnrH1AzZc7L7DmOm1uDuL2JUzJJsZ4zBHe8QorlNctvKQUdn0dkIZqGbjIXJ5gsGHp7ADwQR3GK0rCov/un/dTgAEAa0HeoKXuMvEAAAAASUVORK5CYII=');"></div>
                尊敬的肯西西设计师
                <div style="content: '';display: inline-block;width: 22px;height: 22px;margin-bottom: 5px;background-repeat: no-repeat;background-size: 22px 22px;background: url('data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABYAAAAWCAYAAADEtGw7AAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAA3ppVFh0WE1MOmNvbS5hZG9iZS54bXAAAAAAADw/eHBhY2tldCBiZWdpbj0i77u/IiBpZD0iVzVNME1wQ2VoaUh6cmVTek5UY3prYzlkIj8+IDx4OnhtcG1ldGEgeG1sbnM6eD0iYWRvYmU6bnM6bWV0YS8iIHg6eG1wdGs9IkFkb2JlIFhNUCBDb3JlIDUuNS1jMDE0IDc5LjE1MTQ4MSwgMjAxMy8wMy8xMy0xMjowOToxNSAgICAgICAgIj4gPHJkZjpSREYgeG1sbnM6cmRmPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj4gPHJkZjpEZXNjcmlwdGlvbiByZGY6YWJvdXQ9IiIgeG1sbnM6eG1wTU09Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9tbS8iIHhtbG5zOnN0UmVmPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvc1R5cGUvUmVzb3VyY2VSZWYjIiB4bWxuczp4bXA9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC8iIHhtcE1NOk9yaWdpbmFsRG9jdW1lbnRJRD0ieG1wLmRpZDplZGRiY2YxYS1mMGY5LTQzN2MtYjU3Ny0yMmQxZTE3OWM2NzgiIHhtcE1NOkRvY3VtZW50SUQ9InhtcC5kaWQ6MUY0QTczNEY1MkQ2MTFFNkEwOTI4OTBBQjU0RjJEOTQiIHhtcE1NOkluc3RhbmNlSUQ9InhtcC5paWQ6MUY0QTczNEU1MkQ2MTFFNkEwOTI4OTBBQjU0RjJEOTQiIHhtcDpDcmVhdG9yVG9vbD0iQWRvYmUgUGhvdG9zaG9wIENDIDIwMTUuNSAoTWFjaW50b3NoKSI+IDx4bXBNTTpEZXJpdmVkRnJvbSBzdFJlZjppbnN0YW5jZUlEPSJ4bXAuaWlkOmVkZGJjZjFhLWYwZjktNDM3Yy1iNTc3LTIyZDFlMTc5YzY3OCIgc3RSZWY6ZG9jdW1lbnRJRD0ieG1wLmRpZDplZGRiY2YxYS1mMGY5LTQzN2MtYjU3Ny0yMmQxZTE3OWM2NzgiLz4gPC9yZGY6RGVzY3JpcHRpb24+IDwvcmRmOlJERj4gPC94OnhtcG1ldGE+IDw/eHBhY2tldCBlbmQ9InIiPz7J9zijAAACdklEQVR42tRVTWsTURQ9782bTicSNSoaQ4sIBhddVDEWW8Uqggt3oha7dOm/6Ka/QURddhNwJbiQYhYmtoIIilI01CB+pxVTpCYzk5nnnUk6vDgziZsufDCZ4ebdM+fec94dJqXEdiyObVrM/6mVMDxiYFoyHJUe0lTED3plzRjCY1aAE5coy0jbHBcYw2HpwmQa6szF2/lFVObm4DEC4c4SbtB9NJIMfF/9jHtjM7B74lUY1hpuMondMT2oGpNY4PYysYwB7ZZzID+K43/H2+soxIL6y0O+tYw85xL7+zbLw4lIaEAO81DgtsTvfpuoHXsi1TL86gsskeEpA++oZqvPPiGfI9UT0PCactxEMgy7OKm+QT0ukrqNRGi9456Q0QS+oY37xHwzycNhgixBNE1kNTcQ5RhdR4JNHI4+iflYZm8w1GrgIAH5Fj1D5LLd9tVFyOI8cQA++Ze9hFPk52C5EhuJvRwLbPjBf7YquBTGqfrIyas+hEGqZhUhGoNOGWmwj26hDkQqCjyyExNEVijWWRkEbFs43fMiFys9wPIpTFJ8KhSBo6mbeDWILYk4rtizbk6jFjIrkXiOxHUSwVSO3oukWeGvLw+QsizMMmWYcQ3PAktugU4ZuEyghxQv/hQClUSmNLgcIyCyVxHtY6WJlx1tniDjaLhGG3LKyWqJIdwltutxoJtl5HQe5GQUIg1jB+6w8Y63hSNwhVTMKeW7ro6ingAqi9DaDLM0L9KqFsLBwhZoEHNdPKI/ZPetX2l23B4+ifeJ3p0ha3tYVCbgqmC4xc5iLbLZqeAizeRzRWLzr18IOhBX7TIKyYPof/vm/RFgAP6Z4wdMMi9xAAAAAElFTkSuQmCC');"></div>
            </h1>
        </header>

        <section style="padding-bottom: 100px;width: 800px;margin: 0 auto;text-align: center;">
            <p style="font-size: 12px;line-height: 20px;color: #666;padding: 0;margin: 0;">恭喜您, 通过设计下午茶与业主深入沟通后, 业主最终选择您为其设计。</p>

            <p style="font-size: 12px;line-height: 20px;color: #666;padding: 0;margin: 0;">
                根据规定, 家创易设计师与业主签订设计合同的同时, 家创易、业主、设计师三方需签订
                <a href="javascript:void(0);" style="color: #009DF5;text-decoration: none;outline: none !important;-webkit-tap-highlight-color: transparent;"> &laquo; 项目设计担保交易协议 &raquo; </a>
                , 业主支付全额设计费到家创易进行资金托管, 家创易根据
                <a href="javascript:void(0);" style="color: #009DF5;text-decoration: none;outline: none !important;-webkit-tap-highlight-color: transparent;"> &laquo; 三方合作协议 &raquo; </a>
                分三个阶段支付设计费用给设计师, 并在第一阶段扣除项目佣金15%, 具体操作如下:
            </p>

            <div style="margin-top: 32px;">
                <img src="http://st.jcy.cc/pic_stage.png" style="margin: 0;padding: 0;">
            </div>

            <p style="margin: 32px 0 0 0;font-size: 12px;line-height: 20px;color: #666;padding: 0;">感谢您使用家创易设计师抢单系统, 预祝后续合作愉快!</p>
        </section>

        <footer style="border: none;padding: 0;width: 800px;margin: 0 auto;text-align: center;">
            <div style="margin-top: 0;box-sizing: border-box;font-size: 12px;height: 100px;background: #f6f6f6;padding-top: 30px;">
                <p style="color: #aaa;font-size: 12px;line-height: 20px;padding: 0;margin: 0;">全国免费服务热线 400-830-2323</p>
                <small style="color: #aaa;font-style: normal;display: block;text-align: center;">Copyright © 2015-2016 All Rights Reserved. 粤ICP备15096730号</small>
            </div>
        </footer>
EOF;
        return $content;
    }

    /**
     * 设计师约见业主后,业主未选择该设计师
     */
    private function designer_refused_by_owner(){
        $content =<<<EOF
        <header style="width: 800px;margin: 50px auto 0 auto;text-align: center;overflow: hidden;">
            <h1 style="padding: 0;font-size: 24px;line-height: 32px;margin: 50px;">
                <div style="content: '';display: inline-block;width: 22px;height: 22px;margin-bottom: 5px;background-repeat: no-repeat;background-size: 22px 22px;background: url('data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABYAAAAWCAYAAADEtGw7AAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAA3ppVFh0WE1MOmNvbS5hZG9iZS54bXAAAAAAADw/eHBhY2tldCBiZWdpbj0i77u/IiBpZD0iVzVNME1wQ2VoaUh6cmVTek5UY3prYzlkIj8+IDx4OnhtcG1ldGEgeG1sbnM6eD0iYWRvYmU6bnM6bWV0YS8iIHg6eG1wdGs9IkFkb2JlIFhNUCBDb3JlIDUuNS1jMDE0IDc5LjE1MTQ4MSwgMjAxMy8wMy8xMy0xMjowOToxNSAgICAgICAgIj4gPHJkZjpSREYgeG1sbnM6cmRmPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj4gPHJkZjpEZXNjcmlwdGlvbiByZGY6YWJvdXQ9IiIgeG1sbnM6eG1wTU09Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9tbS8iIHhtbG5zOnN0UmVmPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvc1R5cGUvUmVzb3VyY2VSZWYjIiB4bWxuczp4bXA9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC8iIHhtcE1NOk9yaWdpbmFsRG9jdW1lbnRJRD0ieG1wLmRpZDo3NDcwNjVjMy02MGI5LTRkNTQtODg5Yy04ZjA0Njg1MmRlOTMiIHhtcE1NOkRvY3VtZW50SUQ9InhtcC5kaWQ6MUY0QTczNTM1MkQ2MTFFNkEwOTI4OTBBQjU0RjJEOTQiIHhtcE1NOkluc3RhbmNlSUQ9InhtcC5paWQ6MUY0QTczNTI1MkQ2MTFFNkEwOTI4OTBBQjU0RjJEOTQiIHhtcDpDcmVhdG9yVG9vbD0iQWRvYmUgUGhvdG9zaG9wIENDIDIwMTUuNSAoTWFjaW50b3NoKSI+IDx4bXBNTTpEZXJpdmVkRnJvbSBzdFJlZjppbnN0YW5jZUlEPSJ4bXAuaWlkOjc0NzA2NWMzLTYwYjktNGQ1NC04ODljLThmMDQ2ODUyZGU5MyIgc3RSZWY6ZG9jdW1lbnRJRD0ieG1wLmRpZDo3NDcwNjVjMy02MGI5LTRkNTQtODg5Yy04ZjA0Njg1MmRlOTMiLz4gPC9yZGY6RGVzY3JpcHRpb24+IDwvcmRmOlJERj4gPC94OnhtcG1ldGE+IDw/eHBhY2tldCBlbmQ9InIiPz5/1VEoAAACcklEQVR42tRVz2sTYRB98+1md5vGppRaIXhQS6BYEYpVpK1gbh5EvbiHnvwjPOmlR4+evYgnwRa0ngSFCDapYBArFNGaiyRtFKlt0/zYJt3P2cWkm7i7wYOCe/tmZ943896bXZJS4m88Av/y4SGomsU5K4vrf1AjGq8xU8/isnOm7oTtLIb6CFekjWNugo3H2gWshIJmMNIgXGPwhHO2m3jQAVxZQkJTcMO2oXm4KqtF3CUT+36g9QxGFcKsLaG0YgxaVFuHzeeIK4ITPKCS8N2ysBAJAC1zp0QwO0AlCpF+LLjiyVVo0ShmORjzyLqWL+BeLIWSH+i3NGI6dwoJHQetrjxZx32awJYLXNvCGW7/SPs9YV0bwPy4ib0gXgd0TDOng57x84sFPDV/TSccBzD62XajAo2IjYc0Hgy6+ggsBSbaNYRKcQ/zpocyYS1jlG8bagX2JXI0g3KYC5IJnGZeDU8oczyFeveCjHkDusByT8+qHTXNTwXkfts85jPutRadx04vYPb2oMcFJT8tVEcAOrDXrpXBKTZ4pWRjo3s8z5a1gaWCmlsjsWMY2KBJNNwLrSXcYkk1n/oad/ase+scm8U13PS9EO60i8Y08oJdYAdM3EcKrlbTOOoNHj4UmO9YboC30JQ5xB27BXLK4wmh4WRH8SSqjmAhNXq1jqTg9jdDhSJEfUYOrWEW+gULlgtLYl9/9fmIv+nhmpIwpvCZyfkY0O2P4jbedsff63jnrH1AzZc7L7DmOm1uDuL2JUzJJsZ4zBHe8QorlNctvKQUdn0dkIZqGbjIXJ5gsGHp7ADwQR3GK0rCov/un/dTgAEAa0HeoKXuMvEAAAAASUVORK5CYII=');"></div>
                尊敬的肯西西设计师
                <div style="content: '';display: inline-block;width: 22px;height: 22px;margin-bottom: 5px;background-repeat: no-repeat;background-size: 22px 22px;background: url('data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABYAAAAWCAYAAADEtGw7AAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAA3ppVFh0WE1MOmNvbS5hZG9iZS54bXAAAAAAADw/eHBhY2tldCBiZWdpbj0i77u/IiBpZD0iVzVNME1wQ2VoaUh6cmVTek5UY3prYzlkIj8+IDx4OnhtcG1ldGEgeG1sbnM6eD0iYWRvYmU6bnM6bWV0YS8iIHg6eG1wdGs9IkFkb2JlIFhNUCBDb3JlIDUuNS1jMDE0IDc5LjE1MTQ4MSwgMjAxMy8wMy8xMy0xMjowOToxNSAgICAgICAgIj4gPHJkZjpSREYgeG1sbnM6cmRmPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj4gPHJkZjpEZXNjcmlwdGlvbiByZGY6YWJvdXQ9IiIgeG1sbnM6eG1wTU09Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9tbS8iIHhtbG5zOnN0UmVmPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvc1R5cGUvUmVzb3VyY2VSZWYjIiB4bWxuczp4bXA9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC8iIHhtcE1NOk9yaWdpbmFsRG9jdW1lbnRJRD0ieG1wLmRpZDplZGRiY2YxYS1mMGY5LTQzN2MtYjU3Ny0yMmQxZTE3OWM2NzgiIHhtcE1NOkRvY3VtZW50SUQ9InhtcC5kaWQ6MUY0QTczNEY1MkQ2MTFFNkEwOTI4OTBBQjU0RjJEOTQiIHhtcE1NOkluc3RhbmNlSUQ9InhtcC5paWQ6MUY0QTczNEU1MkQ2MTFFNkEwOTI4OTBBQjU0RjJEOTQiIHhtcDpDcmVhdG9yVG9vbD0iQWRvYmUgUGhvdG9zaG9wIENDIDIwMTUuNSAoTWFjaW50b3NoKSI+IDx4bXBNTTpEZXJpdmVkRnJvbSBzdFJlZjppbnN0YW5jZUlEPSJ4bXAuaWlkOmVkZGJjZjFhLWYwZjktNDM3Yy1iNTc3LTIyZDFlMTc5YzY3OCIgc3RSZWY6ZG9jdW1lbnRJRD0ieG1wLmRpZDplZGRiY2YxYS1mMGY5LTQzN2MtYjU3Ny0yMmQxZTE3OWM2NzgiLz4gPC9yZGY6RGVzY3JpcHRpb24+IDwvcmRmOlJERj4gPC94OnhtcG1ldGE+IDw/eHBhY2tldCBlbmQ9InIiPz7J9zijAAACdklEQVR42tRVTWsTURQ9782bTicSNSoaQ4sIBhddVDEWW8Uqggt3oha7dOm/6Ka/QURddhNwJbiQYhYmtoIIilI01CB+pxVTpCYzk5nnnUk6vDgziZsufDCZ4ebdM+fec94dJqXEdiyObVrM/6mVMDxiYFoyHJUe0lTED3plzRjCY1aAE5coy0jbHBcYw2HpwmQa6szF2/lFVObm4DEC4c4SbtB9NJIMfF/9jHtjM7B74lUY1hpuMondMT2oGpNY4PYysYwB7ZZzID+K43/H2+soxIL6y0O+tYw85xL7+zbLw4lIaEAO81DgtsTvfpuoHXsi1TL86gsskeEpA++oZqvPPiGfI9UT0PCactxEMgy7OKm+QT0ukrqNRGi9456Q0QS+oY37xHwzycNhgixBNE1kNTcQ5RhdR4JNHI4+iflYZm8w1GrgIAH5Fj1D5LLd9tVFyOI8cQA++Ze9hFPk52C5EhuJvRwLbPjBf7YquBTGqfrIyas+hEGqZhUhGoNOGWmwj26hDkQqCjyyExNEVijWWRkEbFs43fMiFys9wPIpTFJ8KhSBo6mbeDWILYk4rtizbk6jFjIrkXiOxHUSwVSO3oukWeGvLw+QsizMMmWYcQ3PAktugU4ZuEyghxQv/hQClUSmNLgcIyCyVxHtY6WJlx1tniDjaLhGG3LKyWqJIdwltutxoJtl5HQe5GQUIg1jB+6w8Y63hSNwhVTMKeW7ro6ingAqi9DaDLM0L9KqFsLBwhZoEHNdPKI/ZPetX2l23B4+ifeJ3p0ha3tYVCbgqmC4xc5iLbLZqeAizeRzRWLzr18IOhBX7TIKyYPof/vm/RFgAP6Z4wdMMi9xAAAAAElFTkSuQmCC');"></div>
            </h1>
        </header>
        <section style="padding-bottom: 100px;width: 800px;margin: 0 auto;text-align: center;">
            <p style="font-size: 12px;line-height: 20px;color: #666;padding: 0;margin: 0;">很遗憾, 通过设计下午茶与业主深入沟通后, 您最终未能成功获得该订单。</p>
            <p style="font-size: 12px;line-height: 20px;color: #666;padding: 0;margin: 0;">原因: 业主选择了其它设计师</p>
        </section>
        <footer style="width: 800px;margin: 0 auto;text-align: center;">
            <div style="margin-top: 0;-webkit-box-sizing: border-box;box-sizing: border-box;font-size: 12px;height: 100px;background: #f6f6f6;padding-top: 30px;">
                <p style="color: #aaa;font-size: 12px;line-height: 20px;margin: 0;padding: 0;">全国免费服务热线 400-830-2323</p>
                <small style="display: block;color: #aaa;font-style: normal;">Copyright © 2015-2016 All Rights Reserved. 粤ICP备15096730号</small>
            </div>
        </footer>
EOF;
        return $content;
    }

    /**
     * 业主订单状态信息
     */
    private function owner_order_status(){
        $content =<<<EOF
        <div style="width: 800px;margin: 50px auto 0 auto;text-align: center;overflow: hidden;">
            <h1 style="padding: 0;font-size: 24px;line-height: 32px;margin: 50px;">
                <div style="content: '';display: inline-block;width: 22px;height: 22px;margin-bottom: 5px;background-repeat: no-repeat;background-size: 22px 22px;background: url('data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABYAAAAWCAYAAADEtGw7AAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAA3ppVFh0WE1MOmNvbS5hZG9iZS54bXAAAAAAADw/eHBhY2tldCBiZWdpbj0i77u/IiBpZD0iVzVNME1wQ2VoaUh6cmVTek5UY3prYzlkIj8+IDx4OnhtcG1ldGEgeG1sbnM6eD0iYWRvYmU6bnM6bWV0YS8iIHg6eG1wdGs9IkFkb2JlIFhNUCBDb3JlIDUuNS1jMDE0IDc5LjE1MTQ4MSwgMjAxMy8wMy8xMy0xMjowOToxNSAgICAgICAgIj4gPHJkZjpSREYgeG1sbnM6cmRmPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj4gPHJkZjpEZXNjcmlwdGlvbiByZGY6YWJvdXQ9IiIgeG1sbnM6eG1wTU09Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9tbS8iIHhtbG5zOnN0UmVmPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvc1R5cGUvUmVzb3VyY2VSZWYjIiB4bWxuczp4bXA9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC8iIHhtcE1NOk9yaWdpbmFsRG9jdW1lbnRJRD0ieG1wLmRpZDo3NDcwNjVjMy02MGI5LTRkNTQtODg5Yy04ZjA0Njg1MmRlOTMiIHhtcE1NOkRvY3VtZW50SUQ9InhtcC5kaWQ6MUY0QTczNTM1MkQ2MTFFNkEwOTI4OTBBQjU0RjJEOTQiIHhtcE1NOkluc3RhbmNlSUQ9InhtcC5paWQ6MUY0QTczNTI1MkQ2MTFFNkEwOTI4OTBBQjU0RjJEOTQiIHhtcDpDcmVhdG9yVG9vbD0iQWRvYmUgUGhvdG9zaG9wIENDIDIwMTUuNSAoTWFjaW50b3NoKSI+IDx4bXBNTTpEZXJpdmVkRnJvbSBzdFJlZjppbnN0YW5jZUlEPSJ4bXAuaWlkOjc0NzA2NWMzLTYwYjktNGQ1NC04ODljLThmMDQ2ODUyZGU5MyIgc3RSZWY6ZG9jdW1lbnRJRD0ieG1wLmRpZDo3NDcwNjVjMy02MGI5LTRkNTQtODg5Yy04ZjA0Njg1MmRlOTMiLz4gPC9yZGY6RGVzY3JpcHRpb24+IDwvcmRmOlJERj4gPC94OnhtcG1ldGE+IDw/eHBhY2tldCBlbmQ9InIiPz5/1VEoAAACcklEQVR42tRVz2sTYRB98+1md5vGppRaIXhQS6BYEYpVpK1gbh5EvbiHnvwjPOmlR4+evYgnwRa0ngSFCDapYBArFNGaiyRtFKlt0/zYJt3P2cWkm7i7wYOCe/tmZ943896bXZJS4m88Av/y4SGomsU5K4vrf1AjGq8xU8/isnOm7oTtLIb6CFekjWNugo3H2gWshIJmMNIgXGPwhHO2m3jQAVxZQkJTcMO2oXm4KqtF3CUT+36g9QxGFcKsLaG0YgxaVFuHzeeIK4ITPKCS8N2ysBAJAC1zp0QwO0AlCpF+LLjiyVVo0ShmORjzyLqWL+BeLIWSH+i3NGI6dwoJHQetrjxZx32awJYLXNvCGW7/SPs9YV0bwPy4ib0gXgd0TDOng57x84sFPDV/TSccBzD62XajAo2IjYc0Hgy6+ggsBSbaNYRKcQ/zpocyYS1jlG8bagX2JXI0g3KYC5IJnGZeDU8oczyFeveCjHkDusByT8+qHTXNTwXkfts85jPutRadx04vYPb2oMcFJT8tVEcAOrDXrpXBKTZ4pWRjo3s8z5a1gaWCmlsjsWMY2KBJNNwLrSXcYkk1n/oad/ase+scm8U13PS9EO60i8Y08oJdYAdM3EcKrlbTOOoNHj4UmO9YboC30JQ5xB27BXLK4wmh4WRH8SSqjmAhNXq1jqTg9jdDhSJEfUYOrWEW+gULlgtLYl9/9fmIv+nhmpIwpvCZyfkY0O2P4jbedsff63jnrH1AzZc7L7DmOm1uDuL2JUzJJsZ4zBHe8QorlNctvKQUdn0dkIZqGbjIXJ5gsGHp7ADwQR3GK0rCov/un/dTgAEAa0HeoKXuMvEAAAAASUVORK5CYII=');"></div>
                尊敬的肯西西先生
                <div style="content: '';display: inline-block;width: 22px;height: 22px;margin-bottom: 5px;background-repeat: no-repeat;background-size: 22px 22px;background: url('data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABYAAAAWCAYAAADEtGw7AAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAA3ppVFh0WE1MOmNvbS5hZG9iZS54bXAAAAAAADw/eHBhY2tldCBiZWdpbj0i77u/IiBpZD0iVzVNME1wQ2VoaUh6cmVTek5UY3prYzlkIj8+IDx4OnhtcG1ldGEgeG1sbnM6eD0iYWRvYmU6bnM6bWV0YS8iIHg6eG1wdGs9IkFkb2JlIFhNUCBDb3JlIDUuNS1jMDE0IDc5LjE1MTQ4MSwgMjAxMy8wMy8xMy0xMjowOToxNSAgICAgICAgIj4gPHJkZjpSREYgeG1sbnM6cmRmPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj4gPHJkZjpEZXNjcmlwdGlvbiByZGY6YWJvdXQ9IiIgeG1sbnM6eG1wTU09Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9tbS8iIHhtbG5zOnN0UmVmPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvc1R5cGUvUmVzb3VyY2VSZWYjIiB4bWxuczp4bXA9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC8iIHhtcE1NOk9yaWdpbmFsRG9jdW1lbnRJRD0ieG1wLmRpZDplZGRiY2YxYS1mMGY5LTQzN2MtYjU3Ny0yMmQxZTE3OWM2NzgiIHhtcE1NOkRvY3VtZW50SUQ9InhtcC5kaWQ6MUY0QTczNEY1MkQ2MTFFNkEwOTI4OTBBQjU0RjJEOTQiIHhtcE1NOkluc3RhbmNlSUQ9InhtcC5paWQ6MUY0QTczNEU1MkQ2MTFFNkEwOTI4OTBBQjU0RjJEOTQiIHhtcDpDcmVhdG9yVG9vbD0iQWRvYmUgUGhvdG9zaG9wIENDIDIwMTUuNSAoTWFjaW50b3NoKSI+IDx4bXBNTTpEZXJpdmVkRnJvbSBzdFJlZjppbnN0YW5jZUlEPSJ4bXAuaWlkOmVkZGJjZjFhLWYwZjktNDM3Yy1iNTc3LTIyZDFlMTc5YzY3OCIgc3RSZWY6ZG9jdW1lbnRJRD0ieG1wLmRpZDplZGRiY2YxYS1mMGY5LTQzN2MtYjU3Ny0yMmQxZTE3OWM2NzgiLz4gPC9yZGY6RGVzY3JpcHRpb24+IDwvcmRmOlJERj4gPC94OnhtcG1ldGE+IDw/eHBhY2tldCBlbmQ9InIiPz7J9zijAAACdklEQVR42tRVTWsTURQ9782bTicSNSoaQ4sIBhddVDEWW8Uqggt3oha7dOm/6Ka/QURddhNwJbiQYhYmtoIIilI01CB+pxVTpCYzk5nnnUk6vDgziZsufDCZ4ebdM+fec94dJqXEdiyObVrM/6mVMDxiYFoyHJUe0lTED3plzRjCY1aAE5coy0jbHBcYw2HpwmQa6szF2/lFVObm4DEC4c4SbtB9NJIMfF/9jHtjM7B74lUY1hpuMondMT2oGpNY4PYysYwB7ZZzID+K43/H2+soxIL6y0O+tYw85xL7+zbLw4lIaEAO81DgtsTvfpuoHXsi1TL86gsskeEpA++oZqvPPiGfI9UT0PCactxEMgy7OKm+QT0ukrqNRGi9456Q0QS+oY37xHwzycNhgixBNE1kNTcQ5RhdR4JNHI4+iflYZm8w1GrgIAH5Fj1D5LLd9tVFyOI8cQA++Ze9hFPk52C5EhuJvRwLbPjBf7YquBTGqfrIyas+hEGqZhUhGoNOGWmwj26hDkQqCjyyExNEVijWWRkEbFs43fMiFys9wPIpTFJ8KhSBo6mbeDWILYk4rtizbk6jFjIrkXiOxHUSwVSO3oukWeGvLw+QsizMMmWYcQ3PAktugU4ZuEyghxQv/hQClUSmNLgcIyCyVxHtY6WJlx1tniDjaLhGG3LKyWqJIdwltutxoJtl5HQe5GQUIg1jB+6w8Y63hSNwhVTMKeW7ro6ingAqi9DaDLM0L9KqFsLBwhZoEHNdPKI/ZPetX2l23B4+ifeJ3p0ha3tYVCbgqmC4xc5iLbLZqeAizeRzRWLzr18IOhBX7TIKyYPof/vm/RFgAP6Z4wdMMi9xAAAAAElFTkSuQmCC');"></div>
            </h1>
        </div>

        <section style="padding-bottom: 100px;width: 800px;margin: 0 auto;text-align: center;">
            <p style="font-size: 12px;line-height: 20px;color: #666;padding: 0;margin: 0;">感谢选择家创易跟您一起打造梦想中的家。您的设计订单已成功提交，我们正在为您匹配最适合您的设计师, 请耐心等待并随时留意订单状态, 我们将持续为您更近最新匹配情况。</p>
            <p style="margin: 32px 0 0 0;font-size: 12px;line-height: 20px;color: #666;padding: 0;">您家的设计师, 不必最贵, 不必太远, 但要最适合。</p>
            <p style="font-size: 12px;line-height: 20px;color: #666;padding: 0;margin: 0;">家创易将最多为您匹配六位合适设计师, 您可以最多选择三位合适者共享“设计下午茶”深度面谈。</p>
            <div style="max-width: 800px;margin-top: 100px;">
                <img src="http://st.jcy.cc/pic_jcy.png" style="display: block;width: 100%;margin: 0;padding: 0;">
            </div>
        </section>

        <footer style="border-top: 1px solid #eee;padding-top: 50px;width: 800px;margin: 0 auto;text-align: center;">
            <ul class="progress-list" style="font-size: 12px;color: #aaa;margin-bottom: 50px;margin-top: 40px;list-style: none;padding: 0;">

                <li style="color: #666;position: relative;float: left;list-style: none;padding: 0;margin: 0;">
                    在线预约<span style="color: #009DF5;">&emsp;&radic;&emsp;</span>
                </li>

                <li style="color: #009DF5;position: relative;float: left;list-style: none;padding: 0;margin: 0;">
                    <span style="position: absolute;top: -40px;left: 5px;background: #009DF5;border-radius: 6px;color: #fff;font-size: 12px;padding: 6px 0;text-align: center;width: 88px;">当前进度
                        <span style="content: '';position: absolute;top: 26;left: 38px;width: 0;height: 0;border-style: solid;border-width: 6px;border-color: transparent;border-top-color: #009DF5;"></span>
                    </span>
                    推荐最匹配设计师<span style="color: #009DF5;">&emsp;&rarr;&emsp;</span>
                </li>

                <li style="position: relative;float: left;list-style: none;padding: 0;margin: 0;">
                    业主筛选约见<span style="color: #009DF5;">&emsp;&rarr;&emsp;</span>
                </li>

                <li style="position: relative;float: left;list-style: none;padding: 0;margin: 0;">
                    深入面谈<span style="color: #009DF5;">&emsp;&rarr;&emsp;</span>
                </li>

                <li style="position: relative;float: left;list-style: none;padding: 0;margin: 0;">
                    线下签合同<span style="color: #aaa;">&emsp;&rarr;&emsp;</span>
                </li>

                <li style="position: relative;float: left;list-style: none;padding: 0;margin: 0;">
                    定制个性化设计<span style="color: #aaa;">&emsp;&rarr;&emsp;</span>
                </li>

                <li style="position: relative;float: left;list-style: none;padding: 0;margin: 0;">
                    装修无缝对接<span style="color: #aaa;">&emsp;&rarr;&emsp;</span>
                </li>

                <li style="position: relative;float: left;list-style: none;padding: 0;margin: 0;">
                    完工/点评
                </li>

                <div style="content: '';display: table;clear: both;"></div>
            </ul>

            <div style="margin-top: 0;box-sizing: border-box;font-size: 12px;height: 100px;background: #f6f6f6;padding-top: 30px;">
                <p style="color: #aaa;font-size: 12px;line-height: 20px;margin: 0;padding: 0;">全国免费服务热线 400-830-2323</p>
                <small style="display: block;color: #aaa;font-style: normal;text-align: center;">Copyright © 2015-2016 All Rights Reserved. 粤ICP备15096730号</small>
            </div>
        </footer>
EOF;
        return $content;
    }

    /**
     * 业主下午茶信息
     */
    private function owner_order_tea(){
        $content =<<<EOF
        <header style="width: 800px;margin: 50px auto 0 auto;text-align: center;overflow: hidden;">
            <h1 style="padding: 0;font-size: 24px;line-height: 32px;margin: 50px;">
                <div style="content: '';display: inline-block;width: 22px;height: 22px;margin-bottom: 5px;background-repeat: no-repeat;background-size: 22px 22px;background: url('data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABYAAAAWCAYAAADEtGw7AAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAA3ppVFh0WE1MOmNvbS5hZG9iZS54bXAAAAAAADw/eHBhY2tldCBiZWdpbj0i77u/IiBpZD0iVzVNME1wQ2VoaUh6cmVTek5UY3prYzlkIj8+IDx4OnhtcG1ldGEgeG1sbnM6eD0iYWRvYmU6bnM6bWV0YS8iIHg6eG1wdGs9IkFkb2JlIFhNUCBDb3JlIDUuNS1jMDE0IDc5LjE1MTQ4MSwgMjAxMy8wMy8xMy0xMjowOToxNSAgICAgICAgIj4gPHJkZjpSREYgeG1sbnM6cmRmPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj4gPHJkZjpEZXNjcmlwdGlvbiByZGY6YWJvdXQ9IiIgeG1sbnM6eG1wTU09Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9tbS8iIHhtbG5zOnN0UmVmPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvc1R5cGUvUmVzb3VyY2VSZWYjIiB4bWxuczp4bXA9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC8iIHhtcE1NOk9yaWdpbmFsRG9jdW1lbnRJRD0ieG1wLmRpZDo3NDcwNjVjMy02MGI5LTRkNTQtODg5Yy04ZjA0Njg1MmRlOTMiIHhtcE1NOkRvY3VtZW50SUQ9InhtcC5kaWQ6MUY0QTczNTM1MkQ2MTFFNkEwOTI4OTBBQjU0RjJEOTQiIHhtcE1NOkluc3RhbmNlSUQ9InhtcC5paWQ6MUY0QTczNTI1MkQ2MTFFNkEwOTI4OTBBQjU0RjJEOTQiIHhtcDpDcmVhdG9yVG9vbD0iQWRvYmUgUGhvdG9zaG9wIENDIDIwMTUuNSAoTWFjaW50b3NoKSI+IDx4bXBNTTpEZXJpdmVkRnJvbSBzdFJlZjppbnN0YW5jZUlEPSJ4bXAuaWlkOjc0NzA2NWMzLTYwYjktNGQ1NC04ODljLThmMDQ2ODUyZGU5MyIgc3RSZWY6ZG9jdW1lbnRJRD0ieG1wLmRpZDo3NDcwNjVjMy02MGI5LTRkNTQtODg5Yy04ZjA0Njg1MmRlOTMiLz4gPC9yZGY6RGVzY3JpcHRpb24+IDwvcmRmOlJERj4gPC94OnhtcG1ldGE+IDw/eHBhY2tldCBlbmQ9InIiPz5/1VEoAAACcklEQVR42tRVz2sTYRB98+1md5vGppRaIXhQS6BYEYpVpK1gbh5EvbiHnvwjPOmlR4+evYgnwRa0ngSFCDapYBArFNGaiyRtFKlt0/zYJt3P2cWkm7i7wYOCe/tmZ943896bXZJS4m88Av/y4SGomsU5K4vrf1AjGq8xU8/isnOm7oTtLIb6CFekjWNugo3H2gWshIJmMNIgXGPwhHO2m3jQAVxZQkJTcMO2oXm4KqtF3CUT+36g9QxGFcKsLaG0YgxaVFuHzeeIK4ITPKCS8N2ysBAJAC1zp0QwO0AlCpF+LLjiyVVo0ShmORjzyLqWL+BeLIWSH+i3NGI6dwoJHQetrjxZx32awJYLXNvCGW7/SPs9YV0bwPy4ib0gXgd0TDOng57x84sFPDV/TSccBzD62XajAo2IjYc0Hgy6+ggsBSbaNYRKcQ/zpocyYS1jlG8bagX2JXI0g3KYC5IJnGZeDU8oczyFeveCjHkDusByT8+qHTXNTwXkfts85jPutRadx04vYPb2oMcFJT8tVEcAOrDXrpXBKTZ4pWRjo3s8z5a1gaWCmlsjsWMY2KBJNNwLrSXcYkk1n/oad/ase+scm8U13PS9EO60i8Y08oJdYAdM3EcKrlbTOOoNHj4UmO9YboC30JQ5xB27BXLK4wmh4WRH8SSqjmAhNXq1jqTg9jdDhSJEfUYOrWEW+gULlgtLYl9/9fmIv+nhmpIwpvCZyfkY0O2P4jbedsff63jnrH1AzZc7L7DmOm1uDuL2JUzJJsZ4zBHe8QorlNctvKQUdn0dkIZqGbjIXJ5gsGHp7ADwQR3GK0rCov/un/dTgAEAa0HeoKXuMvEAAAAASUVORK5CYII=');"></div>
                尊敬的肯西西先生
                <div style="content: '';display: inline-block;width: 22px;height: 22px;margin-bottom: 5px;background-repeat: no-repeat;background-size: 22px 22px;background: url('data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABYAAAAWCAYAAADEtGw7AAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAA3ppVFh0WE1MOmNvbS5hZG9iZS54bXAAAAAAADw/eHBhY2tldCBiZWdpbj0i77u/IiBpZD0iVzVNME1wQ2VoaUh6cmVTek5UY3prYzlkIj8+IDx4OnhtcG1ldGEgeG1sbnM6eD0iYWRvYmU6bnM6bWV0YS8iIHg6eG1wdGs9IkFkb2JlIFhNUCBDb3JlIDUuNS1jMDE0IDc5LjE1MTQ4MSwgMjAxMy8wMy8xMy0xMjowOToxNSAgICAgICAgIj4gPHJkZjpSREYgeG1sbnM6cmRmPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj4gPHJkZjpEZXNjcmlwdGlvbiByZGY6YWJvdXQ9IiIgeG1sbnM6eG1wTU09Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9tbS8iIHhtbG5zOnN0UmVmPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvc1R5cGUvUmVzb3VyY2VSZWYjIiB4bWxuczp4bXA9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC8iIHhtcE1NOk9yaWdpbmFsRG9jdW1lbnRJRD0ieG1wLmRpZDplZGRiY2YxYS1mMGY5LTQzN2MtYjU3Ny0yMmQxZTE3OWM2NzgiIHhtcE1NOkRvY3VtZW50SUQ9InhtcC5kaWQ6MUY0QTczNEY1MkQ2MTFFNkEwOTI4OTBBQjU0RjJEOTQiIHhtcE1NOkluc3RhbmNlSUQ9InhtcC5paWQ6MUY0QTczNEU1MkQ2MTFFNkEwOTI4OTBBQjU0RjJEOTQiIHhtcDpDcmVhdG9yVG9vbD0iQWRvYmUgUGhvdG9zaG9wIENDIDIwMTUuNSAoTWFjaW50b3NoKSI+IDx4bXBNTTpEZXJpdmVkRnJvbSBzdFJlZjppbnN0YW5jZUlEPSJ4bXAuaWlkOmVkZGJjZjFhLWYwZjktNDM3Yy1iNTc3LTIyZDFlMTc5YzY3OCIgc3RSZWY6ZG9jdW1lbnRJRD0ieG1wLmRpZDplZGRiY2YxYS1mMGY5LTQzN2MtYjU3Ny0yMmQxZTE3OWM2NzgiLz4gPC9yZGY6RGVzY3JpcHRpb24+IDwvcmRmOlJERj4gPC94OnhtcG1ldGE+IDw/eHBhY2tldCBlbmQ9InIiPz7J9zijAAACdklEQVR42tRVTWsTURQ9782bTicSNSoaQ4sIBhddVDEWW8Uqggt3oha7dOm/6Ka/QURddhNwJbiQYhYmtoIIilI01CB+pxVTpCYzk5nnnUk6vDgziZsufDCZ4ebdM+fec94dJqXEdiyObVrM/6mVMDxiYFoyHJUe0lTED3plzRjCY1aAE5coy0jbHBcYw2HpwmQa6szF2/lFVObm4DEC4c4SbtB9NJIMfF/9jHtjM7B74lUY1hpuMondMT2oGpNY4PYysYwB7ZZzID+K43/H2+soxIL6y0O+tYw85xL7+zbLw4lIaEAO81DgtsTvfpuoHXsi1TL86gsskeEpA++oZqvPPiGfI9UT0PCactxEMgy7OKm+QT0ukrqNRGi9456Q0QS+oY37xHwzycNhgixBNE1kNTcQ5RhdR4JNHI4+iflYZm8w1GrgIAH5Fj1D5LLd9tVFyOI8cQA++Ze9hFPk52C5EhuJvRwLbPjBf7YquBTGqfrIyas+hEGqZhUhGoNOGWmwj26hDkQqCjyyExNEVijWWRkEbFs43fMiFys9wPIpTFJ8KhSBo6mbeDWILYk4rtizbk6jFjIrkXiOxHUSwVSO3oukWeGvLw+QsizMMmWYcQ3PAktugU4ZuEyghxQv/hQClUSmNLgcIyCyVxHtY6WJlx1tniDjaLhGG3LKyWqJIdwltutxoJtl5HQe5GQUIg1jB+6w8Y63hSNwhVTMKeW7ro6ingAqi9DaDLM0L9KqFsLBwhZoEHNdPKI/ZPetX2l23B4+ifeJ3p0ha3tYVCbgqmC4xc5iLbLZqeAizeRzRWLzr18IOhBX7TIKyYPof/vm/RFgAP6Z4wdMMi9xAAAAAElFTkSuQmCC');"></div>
            </h1>
        </header>

        <section style="padding-bottom: 100px;width: 800px;margin: 0 auto;text-align: center;">
            <p style="font-size: 12px;line-height: 20px;color: #666;padding: 0;margin: 0;">感谢选择家创易跟您一起打造梦想中的家。您的设计订单已成功提交，我们已通知设计师与您共享“设计下午茶”。同时，家创易会在设计下午茶现场为您进行深度设计需求分析。</p>

            <p style="margin: 32px 0 0 0;font-size: 12px;line-height: 20px;color: #666;padding: 0;">设计下午茶时间: <span style="color: #009DF5;">2016-08-01 15:30</span></p>

            <p style="font-size: 12px;line-height: 20px;color: #666;padding: 0;margin: 0;">地点: <span style="color: #009DF5;">南山科兴科学园A4栋1006</span></p>

            <p style="margin: 32px 0 0 0;font-size: 12px;line-height: 20px;color: #666;padding: 0;">届时请带好户型图等相关资料，如有特别喜欢的家居设计图片，也可一并带到现场。预祝面谈愉快!</p>

            <div style="max-width: 800px;margin-top: 100px;">
                <img src="http://st.jcy.cc/pic_jcy.png" style="display: block;width: 100%;padding: 0;margin: 0;">
            </div>
        </section>

        <footer style="border-top: 1px solid #eee;padding-top: 50px;width: 800px;margin: 0 auto;text-align: center;">
            <ul style="font-size: 12px;color: #aaa;margin-bottom: 50px;margin-top: 40px;list-style: none;padding: 0;">
                <li style="position: relative;float: left;list-style: none;padding: 0;margin: 0;">&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;</li>

                <li style="color: #666;position: relative;float: left;list-style: none;padding: 0;margin: 0;">
                    约谈设计师<span style="color: #009DF5;">&emsp;&radic;&emsp;</span>
                </li>

                <li style="color: #009DF5;position: relative;float: left;list-style: none;padding: 0;margin: 0;">
                    <span style="position: absolute;top: -40px;left: -15px;background: #009DF5;border-radius: 6px;color: #fff;font-size: 12px;padding: 6px 0;text-align: center;width: 88px;">当前进度
                        <span style="content: '';position: absolute;top: 24px;left: 38px;width: 0;height: 0;border-style: solid;border-width: 6px;border-color: transparent;border-top-color: #009DF5;"></span>
                    </span>
                    设计下午茶<span style="color: #009DF5;">&emsp;&rarr;&emsp;</span>
                </li>

                <li style="position: relative;float: left;list-style: none;padding: 0;margin: 0;">
                    深入面谈<span style="color: #009DF5;">&emsp;&rarr;&emsp;</span>
                </li>

                <li style="position: relative;float: left;list-style: none;padding: 0;margin: 0;">
                    线下签合同<span style="color: #aaa;">&emsp;&rarr;&emsp;</span>
                </li>

                <li style="position: relative;float: left;list-style: none;padding: 0;margin: 0;">
                    定制个性化设计<span style="color: #aaa;">&emsp;&rarr;&emsp;</span>
                </li>

                <li style="position: relative;float: left;list-style: none;padding: 0;margin: 0;">
                    装修无缝对接<span style="color: #aaa;">&emsp;&rarr;&emsp;</span>
                </li>

                <li style="position: relative;float: left;list-style: none;padding: 0;margin: 0;">
                    完工/点评
                </li>

                <div style="content: '';display: table;clear: both;"></div>
            </ul>


            <div style="margin-top: 0;box-sizing: border-box;font-size: 12px;height: 100px;background: #f6f6f6;padding-top: 30px;">
                <p style="color: #aaa;font-size: 12px;line-height: 20px;margin: 0;padding: 0;">全国免费服务热线 400-830-2323</p>
                <small style="display: block;color: #aaa;font-style: normal;text-align: center;">Copyright © 2015-2016 All Rights Reserved. 粤ICP备15096730号</small>
            </div>
        </footer>
EOF;
        return $content;
    }

    /**
     * 业主确定设计师
     */
    private function owner_verifyed_designer(){
        $content =<<<EOF
        <header style="width: 800px;margin: 50px auto 0 auto;text-align: center;overflow: hidden;">
            <h1 style="padding: 0;font-size: 24px;line-height: 32px;margin: 50px;">
                <div style="content: '';display: inline-block;width: 22px;height: 22px;margin-bottom: 5px;background-repeat: no-repeat;background-size: 22px 22px;background: url('data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABYAAAAWCAYAAADEtGw7AAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAA3ppVFh0WE1MOmNvbS5hZG9iZS54bXAAAAAAADw/eHBhY2tldCBiZWdpbj0i77u/IiBpZD0iVzVNME1wQ2VoaUh6cmVTek5UY3prYzlkIj8+IDx4OnhtcG1ldGEgeG1sbnM6eD0iYWRvYmU6bnM6bWV0YS8iIHg6eG1wdGs9IkFkb2JlIFhNUCBDb3JlIDUuNS1jMDE0IDc5LjE1MTQ4MSwgMjAxMy8wMy8xMy0xMjowOToxNSAgICAgICAgIj4gPHJkZjpSREYgeG1sbnM6cmRmPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj4gPHJkZjpEZXNjcmlwdGlvbiByZGY6YWJvdXQ9IiIgeG1sbnM6eG1wTU09Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9tbS8iIHhtbG5zOnN0UmVmPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvc1R5cGUvUmVzb3VyY2VSZWYjIiB4bWxuczp4bXA9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC8iIHhtcE1NOk9yaWdpbmFsRG9jdW1lbnRJRD0ieG1wLmRpZDo3NDcwNjVjMy02MGI5LTRkNTQtODg5Yy04ZjA0Njg1MmRlOTMiIHhtcE1NOkRvY3VtZW50SUQ9InhtcC5kaWQ6MUY0QTczNTM1MkQ2MTFFNkEwOTI4OTBBQjU0RjJEOTQiIHhtcE1NOkluc3RhbmNlSUQ9InhtcC5paWQ6MUY0QTczNTI1MkQ2MTFFNkEwOTI4OTBBQjU0RjJEOTQiIHhtcDpDcmVhdG9yVG9vbD0iQWRvYmUgUGhvdG9zaG9wIENDIDIwMTUuNSAoTWFjaW50b3NoKSI+IDx4bXBNTTpEZXJpdmVkRnJvbSBzdFJlZjppbnN0YW5jZUlEPSJ4bXAuaWlkOjc0NzA2NWMzLTYwYjktNGQ1NC04ODljLThmMDQ2ODUyZGU5MyIgc3RSZWY6ZG9jdW1lbnRJRD0ieG1wLmRpZDo3NDcwNjVjMy02MGI5LTRkNTQtODg5Yy04ZjA0Njg1MmRlOTMiLz4gPC9yZGY6RGVzY3JpcHRpb24+IDwvcmRmOlJERj4gPC94OnhtcG1ldGE+IDw/eHBhY2tldCBlbmQ9InIiPz5/1VEoAAACcklEQVR42tRVz2sTYRB98+1md5vGppRaIXhQS6BYEYpVpK1gbh5EvbiHnvwjPOmlR4+evYgnwRa0ngSFCDapYBArFNGaiyRtFKlt0/zYJt3P2cWkm7i7wYOCe/tmZ943896bXZJS4m88Av/y4SGomsU5K4vrf1AjGq8xU8/isnOm7oTtLIb6CFekjWNugo3H2gWshIJmMNIgXGPwhHO2m3jQAVxZQkJTcMO2oXm4KqtF3CUT+36g9QxGFcKsLaG0YgxaVFuHzeeIK4ITPKCS8N2ysBAJAC1zp0QwO0AlCpF+LLjiyVVo0ShmORjzyLqWL+BeLIWSH+i3NGI6dwoJHQetrjxZx32awJYLXNvCGW7/SPs9YV0bwPy4ib0gXgd0TDOng57x84sFPDV/TSccBzD62XajAo2IjYc0Hgy6+ggsBSbaNYRKcQ/zpocyYS1jlG8bagX2JXI0g3KYC5IJnGZeDU8oczyFeveCjHkDusByT8+qHTXNTwXkfts85jPutRadx04vYPb2oMcFJT8tVEcAOrDXrpXBKTZ4pWRjo3s8z5a1gaWCmlsjsWMY2KBJNNwLrSXcYkk1n/oad/ase+scm8U13PS9EO60i8Y08oJdYAdM3EcKrlbTOOoNHj4UmO9YboC30JQ5xB27BXLK4wmh4WRH8SSqjmAhNXq1jqTg9jdDhSJEfUYOrWEW+gULlgtLYl9/9fmIv+nhmpIwpvCZyfkY0O2P4jbedsff63jnrH1AzZc7L7DmOm1uDuL2JUzJJsZ4zBHe8QorlNctvKQUdn0dkIZqGbjIXJ5gsGHp7ADwQR3GK0rCov/un/dTgAEAa0HeoKXuMvEAAAAASUVORK5CYII=');"></div>
                尊敬的肯西西先生
                <div style="content: '';display: inline-block;width: 22px;height: 22px;margin-bottom: 5px;background-repeat: no-repeat;background-size: 22px 22px;background: url('data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABYAAAAWCAYAAADEtGw7AAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAA3ppVFh0WE1MOmNvbS5hZG9iZS54bXAAAAAAADw/eHBhY2tldCBiZWdpbj0i77u/IiBpZD0iVzVNME1wQ2VoaUh6cmVTek5UY3prYzlkIj8+IDx4OnhtcG1ldGEgeG1sbnM6eD0iYWRvYmU6bnM6bWV0YS8iIHg6eG1wdGs9IkFkb2JlIFhNUCBDb3JlIDUuNS1jMDE0IDc5LjE1MTQ4MSwgMjAxMy8wMy8xMy0xMjowOToxNSAgICAgICAgIj4gPHJkZjpSREYgeG1sbnM6cmRmPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj4gPHJkZjpEZXNjcmlwdGlvbiByZGY6YWJvdXQ9IiIgeG1sbnM6eG1wTU09Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9tbS8iIHhtbG5zOnN0UmVmPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvc1R5cGUvUmVzb3VyY2VSZWYjIiB4bWxuczp4bXA9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC8iIHhtcE1NOk9yaWdpbmFsRG9jdW1lbnRJRD0ieG1wLmRpZDplZGRiY2YxYS1mMGY5LTQzN2MtYjU3Ny0yMmQxZTE3OWM2NzgiIHhtcE1NOkRvY3VtZW50SUQ9InhtcC5kaWQ6MUY0QTczNEY1MkQ2MTFFNkEwOTI4OTBBQjU0RjJEOTQiIHhtcE1NOkluc3RhbmNlSUQ9InhtcC5paWQ6MUY0QTczNEU1MkQ2MTFFNkEwOTI4OTBBQjU0RjJEOTQiIHhtcDpDcmVhdG9yVG9vbD0iQWRvYmUgUGhvdG9zaG9wIENDIDIwMTUuNSAoTWFjaW50b3NoKSI+IDx4bXBNTTpEZXJpdmVkRnJvbSBzdFJlZjppbnN0YW5jZUlEPSJ4bXAuaWlkOmVkZGJjZjFhLWYwZjktNDM3Yy1iNTc3LTIyZDFlMTc5YzY3OCIgc3RSZWY6ZG9jdW1lbnRJRD0ieG1wLmRpZDplZGRiY2YxYS1mMGY5LTQzN2MtYjU3Ny0yMmQxZTE3OWM2NzgiLz4gPC9yZGY6RGVzY3JpcHRpb24+IDwvcmRmOlJERj4gPC94OnhtcG1ldGE+IDw/eHBhY2tldCBlbmQ9InIiPz7J9zijAAACdklEQVR42tRVTWsTURQ9782bTicSNSoaQ4sIBhddVDEWW8Uqggt3oha7dOm/6Ka/QURddhNwJbiQYhYmtoIIilI01CB+pxVTpCYzk5nnnUk6vDgziZsufDCZ4ebdM+fec94dJqXEdiyObVrM/6mVMDxiYFoyHJUe0lTED3plzRjCY1aAE5coy0jbHBcYw2HpwmQa6szF2/lFVObm4DEC4c4SbtB9NJIMfF/9jHtjM7B74lUY1hpuMondMT2oGpNY4PYysYwB7ZZzID+K43/H2+soxIL6y0O+tYw85xL7+zbLw4lIaEAO81DgtsTvfpuoHXsi1TL86gsskeEpA++oZqvPPiGfI9UT0PCactxEMgy7OKm+QT0ukrqNRGi9456Q0QS+oY37xHwzycNhgixBNE1kNTcQ5RhdR4JNHI4+iflYZm8w1GrgIAH5Fj1D5LLd9tVFyOI8cQA++Ze9hFPk52C5EhuJvRwLbPjBf7YquBTGqfrIyas+hEGqZhUhGoNOGWmwj26hDkQqCjyyExNEVijWWRkEbFs43fMiFys9wPIpTFJ8KhSBo6mbeDWILYk4rtizbk6jFjIrkXiOxHUSwVSO3oukWeGvLw+QsizMMmWYcQ3PAktugU4ZuEyghxQv/hQClUSmNLgcIyCyVxHtY6WJlx1tniDjaLhGG3LKyWqJIdwltutxoJtl5HQe5GQUIg1jB+6w8Y63hSNwhVTMKeW7ro6ingAqi9DaDLM0L9KqFsLBwhZoEHNdPKI/ZPetX2l23B4+ifeJ3p0ha3tYVCbgqmC4xc5iLbLZqeAizeRzRWLzr18IOhBX7TIKyYPof/vm/RFgAP6Z4wdMMi9xAAAAAElFTkSuQmCC');"></div>
            </h1>
        </header>

        <section style="padding-bottom: 100px;width: 800px;margin: 0 auto;text-align: center;">
            <p style="font-size: 12px;line-height: 20px;color: #666;padding: 0;margin: 0;">您已选择Kencc设计师为您打造专属设计, 在接下来的设计过程中, 家创易将继续与您携手, 为您的设计之旅全程保障。</p>
            <p style="font-size: 12px;line-height: 20px;color: #666;padding: 0;margin: 0;">除了与设计师签订一份项目设计合同, 您将与家创易、设计师共同签订一份<a href="javascript:void(0);" style="color: #009DF5;text-decoration: none;"> &laquo; 设计担保协议  (三方合同) &raquo; </a>, 家创易将为您全额托管设计费, 在您确认设计满意后分阶段付费给设计师, 具体为: </p>
            <div style="margin: 32px;">
                <img src="http://st.jcy.cc/pic_stage.png" style="margin: 0;padding: 0;">
            </div>
            <p style="margin: 32px 0 0 0;font-size: 12px;line-height: 20px;color: #666;padding: 0;">设计费用将在您确认各阶段设计工作满意后支付, 感谢选择家创易跟您一起打造梦想中的家。</p>
        </section>


        <footer style="border-top: 1px solid #eee;padding-top: 50px;width: 800px;margin: 0 auto;text-align: center;">
            <ul style="font-size: 12px;color: #aaa;margin-bottom: 50px;margin-top: 40px;list-style: none;padding: 0;">

                <li style="color: #666;position: relative;float: left;list-style: none;padding: 0;margin: 0;">
                    在线预约<span style="color: #009DF5;">&emsp;&radic;&emsp;</span>
                </li>

                <li style="color: #666;position: relative;float: left;list-style: none;padding: 0;margin: 0;">
                    推荐最匹配设计师<span style="color: #009DF5;">&emsp;&radic;&emsp;</span>
                </li>

                <li style="color: #666;position: relative;float: left;list-style: none;padding: 0;margin: 0;">
                    业主筛选约见<span style="color: #009DF5;">&emsp;&radic;&emsp;</span>
                </li>

                <li style="color: #666;position: relative;float: left;list-style: none;padding: 0;margin: 0;">
                    深入面谈<span style="color: #009DF5;">&emsp;&radic;&emsp;</span>
                </li>

                <li style="color: #009DF5;position: relative;float: left;list-style: none;padding: 0;margin: 0;">
                    <span style="position: absolute;top: -40px;left: -15px;background: #009DF5;border-radius: 6px;color: #fff;font-size: 12px;padding: 6px 0;text-align: center;width: 88px;">当前进度
                        <span style="content: '';position: absolute;top: 24px;left: 38px;width: 0;height: 0;border-style: solid;border-width: 6px;border-color: transparent;border-top-color: #009DF5;"></span>
                    </span>
                    线下签合同<span style="color: #aaa;">&emsp;&rarr;&emsp;</span>
                </li>

                <li style="position: relative;float: left;list-style: none;padding: 0;margin: 0;">
                    定制个性化设计<span style="color: #aaa;">&emsp;&rarr;&emsp;</span>
                </li>

                <li style="position: relative;float: left;list-style: none;padding: 0;margin: 0;">
                    装修无缝对接<span style="color: #aaa;">&emsp;&rarr;&emsp;</span>
                </li>

                <li style="position: relative;float: left;list-style: none;padding: 0;margin: 0;">
                    完工/点评
                </li>

                <div style="content: '';display: table;clear: both;"></div>
            </ul>


            <div style="margin-top: 0;box-sizing: border-box;font-size: 12px;height: 100px;background: #f6f6f6;padding-top: 30px;">
                <p style="color: #aaa;font-size: 12px;line-height: 20px;margin: 0;padding: 0;">全国免费服务热线 400-830-2323</p>
                <small style="display: block;color: #aaa;font-style: normal;text-align: center;">Copyright © 2015-2016 All Rights Reserved. 粤ICP备15096730号</small>
            </div>
        </footer>
EOF;
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




    public function getDesignerSignUpAccessEmail($realname){

        $content =<<<EOF

<div class="mail-box" style="max-width: 640px;
            margin: 0 auto;
            font-size: 16px;
            color: #4C4C4C;
            font-family: 'Helvetica Neue,Helvetica,Arial,sans-serif';">
<header style="border-bottom:1px solid #aaa;">
    <div class="head-box" style="width: 90%;margin: 3% auto;font-weight: bold;">
        <h5 style="margin: 0; padding:0;margin-bottom: 3%;font-size: 1.2rem;line-height: 1.5rem;">新消息/家创易设计师注册成功通知</h5>
        <p style="margin: 0;padding:0;display: inline-block;font-size: 0.9rem;line-height: 1.2rem;">家创易设计师服务中心</p>
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
            line-height: 2.0rem;">{$realname}设计师:</h6>
    <p style="margin: 0;padding:0;font-size: 1.2rem;
            line-height: 2.0rem;">欢迎来到家创易，你已成功通过家创易认证审核，赶快登录并上传案例吧。</p>
   
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
            margin-left: 0.6rem;">请登录<span style="margin-left: 0.6rem;"><a href="http://www.jcy.cc" style="color: #009DF5;">http://www.jcy.cc</a></span></p>
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
            <span>深圳市家创易科技有限公司版权所有</span>
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
     *  通知业主已发布需求=>step 1.
     */
//    public function homeown_notice_order_access($params){
//
//        $this->initMail();
//        $this->sendmail($params, '成功发布需求', $this->getOrderAccessPage());
//
//    }



    /**
     *  通知设计师抢单
     */
    public function designer_notice_grab_email($params){

        $this->initMail();

        foreach ($params['tomail'] as $index_id=>$designer_info){
//            var_dump($designer_info);
            $this->sendmail(array('to'=>$designer_info['mail']), '设计订单通知', $this->designer_notice_grab($designer_info['uid'], $params['design_order_id']));
        }

    }



    public function designerSignUpAccessEmail($params){
        $this->initMail();
        $this->sendmail(array('to'=>$params['to']), '个人信息审核通过', $this->getDesignerSignUpAccessEmail($params['realname']));

    }


}
