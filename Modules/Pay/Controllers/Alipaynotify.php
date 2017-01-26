<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/7/22
 * Time: 11:27
 */
namespace Module\Pay\Controllers;
use \Module\Pay\Controller as Controller;


class Alipaynotify extends Controller
{

    function logW($word='') {

        $fl=__DIR__.'/../configs/log.txt';
        $fp = fopen($fl,"a");
        flock($fp, LOCK_EX) ;
        fwrite($fp,"执行日期：".date("Y-m-d H:m:s",time())."\n".$word."\n");
        flock($fp, LOCK_UN);
        fclose($fp);
    }

//    public function aa(){
//        $this->logW();
//    }

    public function notify($params){
        $CALLBACK_DATA=$params['CALLBACK_DATA'];
//

        $this->logW('进入付款通知接口');

        $AlipayWap = $this->Lib("AlipayWap");
        $alipayNotify=$AlipayWap->getAliPayClass('AlipayNotify');
        $verify_result = $alipayNotify->verifyNotify($CALLBACK_DATA);

        if($verify_result) {//验证成功
            $this->logW('验证成功');

            /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
            //请在这里加上商户的业务逻辑程序代

            //——请根据您的业务逻辑来编写程序（以下代码仅作参考）——

            //获取支付宝的通知返回参数，可参考技术文档中服务器异步通知参数列表

            //商户订单号

            $out_trade_no = $CALLBACK_DATA['out_trade_no'];

            $this->logW('返回的订单号：'.$out_trade_no);

            //支付宝交易号

            $trade_no = $CALLBACK_DATA['trade_no'];

            //交易状态
            $trade_status = $CALLBACK_DATA['trade_status'];

            $this->logW('返回的订单交易状态：'.$trade_status);

            $designer_yuyue_compete_info=array();
            $sort=1;
            if($CALLBACK_DATA['trade_status'] == 'TRADE_FINISHED' || $CALLBACK_DATA['trade_status'] == 'TRADE_SUCCESS') {
                $designer_yuyue_compete= model("Orders/designer_yuyue_compete");
                $designer_yuyue_compete_info = $designer_yuyue_compete->detail(array('orderNo'=>$out_trade_no,'status'=>'0'));

                $designer_yuyue_compete->set($designer_yuyue_compete_info['id'],
                    array('status'=>'1','pay_time'=>time(),
                        'buyer_email'=>$CALLBACK_DATA['buyer_email'],
                        'trade_no'=>$CALLBACK_DATA['trade_no'],
                        'trade_status'=>$CALLBACK_DATA['trade_status'],
                        'payment'=>$CALLBACK_DATA['price'],
//                        'sort'=>$sort
                    ));

                $yuyue_id = $designer_yuyue_compete_info['yuyue_id'];
                $member = model("ucenter/member");
                $designer_info = $member->get($designer_yuyue_compete_info['designer_id']);


                $designer_yuyue= model("orders/designer_yuyue");
                $designer_yuyue_info = $designer_yuyue->detail(array('yuyue_id'=>$yuyue_id));
                $http = $this->Lib("Http");
                if((int)$designer_yuyue_info['type'] == 1){//预约指定设计师

//                    $http->designerGrabOrderSuccess(array(
//
//                        'to_designer_mail'=>array(
//                        )
//                    ));

                    //todo  是否需要发送邮件通知

                    if($designer_yuyue_info['course_key']=='access_sys'){
                        $designer_yuyue->set($yuyue_id,array(
                            'course_key'=>'interview',
                        ));
                    }

                }elseif((int)$designer_yuyue_info['type'] == 2){//为你推荐
                    $sort = $designer_yuyue_info['sort']+1;

//                    if($designer_yuyue_info['course_key']=='access_sys'){
//                        $designer_yuyue->set($yuyue_id,array(
//                            'course_key'=>'accept_dsg',
//                        ));
//                    }

                    $designer_yuyue->set($yuyue_id,array(
                        'sort'=>$sort
                    ));

                    $designer_yuyue_compete->set($designer_yuyue_compete_info['id'],
                        array(
                            'sort'=>$sort
                        ));



                    $conent = json_decode($designer_yuyue_info['content'],true);

                    $member_info = $member->get($designer_yuyue_info['uid']);


                    $http->designerGrabOrderSuccess(array(

                        'to_designer_mail'=>array(
                            "sort"=>$sort,
                            "realname"=>$designer_info['realname'],
                            "to" => $designer_info['mail'],
                            "price" =>$CALLBACK_DATA['price'],
                            "yuyue_sn" =>$designer_yuyue_info['yuyue_sn'],
                            "date" =>$designer_yuyue_info['interview_time'],
                            "area_name" =>$conent['city'].'-'.$conent['city_area'],
                            "home_name" =>$conent['home'],
                            "project_type" =>$conent['project_type'],
                            "area_size" =>$conent['area'],
                        )
                    ));

                    $yuyue_compete=model('orders/designer_yuyue_compete');
                    $grab = $yuyue_compete->grabDesignerCount($yuyue_id);


                    if((int)$grab['grabCount']>=3){

                        $http->designerGrabOrderfinished(array(

                            'to_homeOwner_mail'=>array(
                                "realname"=>$member_info['realname'],
                                "to" => $member_info['mail'],
                                "grabCount"=>$grab['grabCount'],
                                "price" =>$CALLBACK_DATA['price'],
                                "yuyue_sn" =>$designer_yuyue_info['yuyue_sn'],
                                "date" =>$designer_yuyue_info['interview_time'],
                                "address" =>$designer_yuyue_info['interview_add'],
                                "area_name" =>$conent['city'].'-'.$conent['city_area'],
                                "home_name" =>$conent['home'],
                                "project_type" =>$conent['project_type'],
                                "area_size" =>$conent['area'],
                            )
                        ));
                    }
                }
            }



            //——请根据您的业务逻辑来编写程序（以上代码仅作参考）——
//            echo "success";		//请不要修改或删除
            $this->ret['code']=-1;
            $this->ret['msg']='success';

            /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        }else {
            $this->logW('验证失败');
            //验证失败
            $this->ret['code']=-1;
            $this->ret['msg']='fail';
//            echo "fail";

            //调试用，写文本函数记录程序运行情况是否正常
            //logResult("这里写入想要调试的代码变量值，或其他运行的结果记录");
        }

    }

}