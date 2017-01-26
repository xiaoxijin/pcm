<?php

namespace Module\Orders\Controllers;
use \Module\Orders\Controller as Controller;

class OrderExpired extends Controller
{

    public function yuyueOrderExpired($params){

        $yuyueInfo = $this->Model('Designer_yuyue')->get($params['designer_order_id']);
        if((int)$yuyueInfo['choose']==1)
            return true;

        $grab = $this->Model('Designer_yuyue_compete')->grabDesignerCount($params['designer_order_id']);
        if((int)$grab['grabCount']>=2){
            return true;
        }else{
            $this->Model('Designer_yuyue')->set($params['designer_order_id'],array('course_key'=>'nointerview'));
            $this->Model('Designer_yuyue_compete')->sets(array('status'=>'3'),array('status'=>'1','trade_status'=>'TRADE_SUCCESS'));
            return true;
        }

    }

    //预约指定设计师——判断设计师是否付款 规则（审核订单通过后24小时内，下午茶见面时间前4小时）
    public function appointOrderExpired($params){

        $yuyueInfo = $this->Model('Designer_yuyue')->get($params['designer_order_id']);

        if($yuyueInfo['course_key']=='interview'){
            return true;
        }else{
            $this->Model('Designer_yuyue')->set($params['designer_order_id'],array('course_key'=>'nopay'));
            return true;
        }


    }

}
