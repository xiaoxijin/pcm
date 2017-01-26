<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/7/22
 * Time: 11:27
 */
namespace Module\Pay\Controllers;
use \Module\Pay\Controller as Controller;


class Jcypay extends Controller
{

    public function go($params){
        $uid = $params['uid'];
        $orderNo = $params['yuyue_sn'];
        $paymode = $params['paymode'];
        $desMessage = $params['desMessage'];
        $return_url = $params['return_url'];

        if(is_int($uid) and !empty($uid) and is_int($paymode) and !empty($paymode) and is_string($orderNo) and !empty($orderNo)){

            $designer_yuyue_compete = model("Orders/designer_yuyue_compete");
            $designer_yuyue_compete_info = $designer_yuyue_compete->detail(array('orderNo'=>$orderNo));

            $yuyue_id = $designer_yuyue_compete_info['yuyue_id'];

            $designer_yuyue= model("orders/designer_yuyue");
            $designer_yuyue_info = $designer_yuyue->detail(array('yuyue_id'=>$yuyue_id));

            if((int)$designer_yuyue_info['type'] == 1){
                $exporedSecond = 86400;
            }elseif((int)$designer_yuyue_info['type'] == 2){
                $exporedSecond = 300;
            }

            $designer_yuyue_compete->getPayOrder($uid,$orderNo,$exporedSecond,$result);
            if($result['msg']==NO_RESULT){
                $this->setRet(array('code'=>NO_RESULT));
            }else{
                if(!empty($paymode) && $paymode!=$result['data']['3']){
                    model('orders/designer_yuyue_compete')->set($result['data']['2'],array('dsgIntro'=>$desMessage));
                }
                $orderNo = $result['data']['0'];
                $price = $result['data']['1'];
                if($paymode=='1'){
                    $alipay_config=$this->config['alipaywap'];
                    $parameter = array(
                        "service"       => $alipay_config['service'],
                        "partner"       => $alipay_config['partner'],
                        "seller_id"  => $alipay_config['seller_id'],
                        "payment_type"	=> $alipay_config['payment_type'],
                        "notify_url"	=> $alipay_config['notify_url'],
                        "return_url"	=> $return_url,
                        "_input_charset"	=> trim(strtolower($alipay_config['input_charset'])),
                        "out_trade_no"	=>$orderNo,
                        "subject"	=> '设计下午茶',
                        "total_fee"	=>$price,
                        "show_url"	=> "http://ds.jcy.cc",
                        "app_pay"	=> "Y",//启用此参数能唤起钱包APP支付宝
                        //其他业务参数根据在线开发文档，添加参数.文档地址:https://doc.open.alipay.com/doc2/detail.htm?spm=a219a.7629140.0.0.2Z6TSk&treeId=60&articleId=103693&docType=1
                        //如"参数名"	=> "参数值"   注：上一个参数末尾需要“,”逗号。

                    );
                    $AlipayWap = $this->Lib("AlipayWap");
                    $objAlipaySubmit=$AlipayWap->getAliPayClass('AlipaySubmit');
                    $html_text = $objAlipaySubmit->buildRequestForm($parameter,"get", "确认");
                    $html_text="<!DOCTYPE html><html><head><meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\"><title>支付宝手机网站支付接口接口</title></head>".$html_text;
                    $this->ret['data'] = array("html"=>$html_text);
                    $this->ret['msg'] = "OK";
                    $this->ret['code'] = 0;
                }
            }
        }else{
            $this->setRet(array('code'=>PARAM_ERR));
        }
    }
}