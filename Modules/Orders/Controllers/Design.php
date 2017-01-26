<?php

namespace Module\Orders\Controllers;
use \Module\Orders\Controller as Controller;

class Design extends Controller
{

    public function getAllOrder($params){


        $designer_id = $params['designer_id'];
        if($designer_id>0){
            $yuyue = $this->Model("designer_yuyue");
            $ordersInfo = $yuyue->getOrders($designer_id);

            $member_info = model("ucenter/member")->get($designer_id);

            $this->setRet(array('data'=>array(
                "designer_id"=>$designer_id,
                "designer_name"=>$member_info['realname'],
                "ordersCount"=>count($ordersInfo),
                "orders"=>$ordersInfo)));
        }
    }



    public function LockallMatch($params){
        $yuyue_sn = $params['yuyue_sn'];
        $uid = $params['uid'];
        $p = $params['page_number'];
        $l = $params['page_size'];
        if (is_int($uid) and !empty($uid)){//判断设计师uid是否正确


            $this->Model('designer_yuyue')->MatchAllOrder($uid,$yuyue_sn,$p,$l,$result,$count);

            if($result['msg']==NO_ACCEPT_CITY_ID){////当前设计师接单范围不明
                $this->setRet(array('code'=>NO_ACCEPT_CITY_ID));
            }elseif(!isset($result['data']) Or $result['msg']==NO_RESULT){
                $this->setRet(array('code'=>NO_RESULT));
            }else{

                $this->ret['data'] = array("orders"=>$result['data'],"count"=>$count);
                $this->ret['msg'] = "OK";
                $this->ret['code'] = 0;

            }
        }else{
            $this->setRet(array('code'=>PARAM_ERR));
        }
    }

    //立即抢单，申请绑定业主订单
    public function Lock($params){
        $yuyue_sn = $params['yuyue_sn'];
        $uid = $params['uid'];
        $pay = $params['pay'];
        if(is_int($uid) and !empty($uid) and is_string($yuyue_sn) and !empty($yuyue_sn)){
            if(is_int($uid) and $pay=='1'){//从已抢订单页面去付款页面
                $this->Model('designer_yuyue_compete')->CompeteOrderToPay($uid,$yuyue_sn,$result);
                if($result['msg']=='have_pay'){//已经付款
                    $this->setRet(array('code'=>DESIGNER_ORDER_PAYED));
                }elseif($result['msg']=='pay_overtime'){//付款超时
                    $this->Lock($params);
//                    $this->setRet(array('code'=>DESIGNER_ORDER_PAY_OVERTIME));
                }else{
                    $this->ret['data'] = array("orders"=>$result['data'],"count"=>1);
                    $this->ret['msg'] = "OK";
                    $this->ret['code'] = 0;
                }
            }else{
                $this->Model('designer_yuyue')->MatchAllOrder($uid,$yuyue_sn,null,null,$result,$count);
                if($result['msg']=='have_pay'){//已经付款
                    $this->setRet(array('code'=>DESIGNER_ORDER_PAYED));
                }elseif($result['msg']=='pay_overtime'){//付款超时
//                    unset($params['yuyue_sn']);
                    $this->Lock($params);
//                    $this->setRet(array('code'=>DESIGNER_ORDER_PAY_OVERTIME));
                }elseif($result['msg']=='0809'){//刷新付款页面
                    $this->ret['data'] = array("orders"=>$result['data'],"count"=>$count);
                    $this->ret['msg'] = "OK";
                    $this->ret['code'] = 0;
                }elseif($result['msg']==NO_ACCEPT_CITY_ID){////当前设计师接单范围不明
                    $this->setRet(array('code'=>NO_ACCEPT_CITY_ID));
                }elseif($result['msg']==NO_RESULT){
                    $this->setRet(array('code'=>NO_RESULT));
                }else{
                    $orderInfo=$result['data']['0'];
                    //产生订单号
                    $year_code = array('A','B','C','D','E','F','G','H','I','J','k','L','M','N','O');
                    $orderNo = 'd'.$year_code[intval(date('Y'))-2016].strtoupper(dechex(date('m'))).date('d').substr(time(),-3).substr(microtime(),2,2).sprintf('%d',rand(0,9));
                    $result['data']['0']['yuyue_sn']=$orderNo;

                    $result['data']['0']['rank'] = 7-$result['data']['0']['spare_spots'];
                    $result['data']['0']['num'] = 7-$result['data']['0']['spare_spots'];

                    $add_time=time();
                    $result['data']['0']['fail_time'] = $add_time + 5*60;
                    $competeId=$this->Model('designer_yuyue_compete')->add(
                        array('orderNo'=>$orderNo,'yuyue_id'=>$orderInfo['yuyue_id'],
                            'designer_id'=>$uid,'price'=>$orderInfo['get_price'],
                            'add_time'=>$add_time));
                    if($competeId){

                        $this->ret['data'] = array("orders"=>$result['data'],"count"=>$count);
                        $this->ret['msg'] = "OK";
                        $this->ret['code'] = 0;
                    }else{
                        $this->setRet(array('code'=>DESIGNER_COMPETE_ERROR));
                    }
                }
            }

        }else{
            $this->setRet(array('code'=>PARAM_ERR));
        }
    }


    //获得已抢订单
    public function competed($params){
        $uid = $params['uid'];
        $p = $params['page_number'];
        $l = $params['page_size'];
        if(is_int($uid) and !empty($uid)){
            $this->Model('designer_yuyue_compete')->getCompeteOrders($uid,$p,$l,$result,$count);
            if($result['msg']==NO_RESULT){
                $this->setRet(array('code'=>NO_RESULT));
            }else{
                $this->ret['data'] = array("orders"=>$result['data'],"count"=>$count);
                $this->ret['msg'] = "OK";
                $this->ret['code'] = 0;
            }
        }else{
            $this->setRet(array('code'=>PARAM_ERR));
        }
    }


    //预约订单  --- 业主预约指定设计师订单
    public function appointOrder($params){
        $uid = $params['uid'];
        $p = $params['page_number'];
        $l = $params['page_size'];
        if(is_int($uid) and !empty($uid)){
            $this->Model('designer_yuyue_compete')->getAppointOrders($uid,$p,$l,$result,$count);
            if($result['msg']==NO_RESULT){
                $this->setRet(array('code'=>NO_RESULT));
            }else{
                $this->ret['data'] = array("orders"=>$result['data'],"count"=>$count);
                $this->ret['msg'] = "OK";
                $this->ret['code'] = 0;
            }
        }else{
            $this->setRet(array('code'=>PARAM_ERR));
        }

    }

    //预约订单(业主预约指定设计师订单) 去付款页面
    public function payAppointOrder($params){
        $yuyue_sn = $params['yuyue_sn'];
        $uid = $params['uid'];
        if(is_int($uid) and !empty($uid) and is_string($yuyue_sn) and !empty($yuyue_sn)){
            $this->Model('designer_yuyue_compete')->AppointOrderToPay($uid,$yuyue_sn,$result);
            if($result['msg']==NO_RESULT){
                $this->setRet(array('code'=>NO_RESULT));
            }elseif($result['msg']=='have_pay'){//已经付款
                $this->setRet(array('code'=>DESIGNER_ORDER_PAYED));
            }elseif($result['msg']=='pay_overtime'){//付款超时
                $this->setRet(array('code'=>DESIGNER_ORDER_PAY_OVERTIME));
            }else{
                $this->ret['data'] = array("orders"=>$result['data'],"count"=>1);
                $this->ret['msg'] = "OK";
                $this->ret['code'] = 0;
            }
        }else{
            $this->setRet(array('code'=>PARAM_ERR));
        }


    }

    public function history($params)
    {
        $uid = $params['uid'];
        $number = $params['number'];
        $unit = $params['unit'];
        // 用来保存结果
        $data = array();
        $tmp = array();
        if(!empty($uid) and !empty($number)){
            if(is_string($uid)) $uid = intval($uid);
            if(is_string($number)) $number = intval($number);
            if(is_string($unit) and !empty($unit)){
                $res = $this->Model('designer_yuyue_compete')->getAllHistory($uid);
               // $this->ret['code'] = array("orders"=>$tmp);
                foreach($res as $k=>$v){
                        switch($unit){
                            case "weeks":
                                $diff = $number*7*24*3600;
                                if( $diff >= (time() - $v['add_time'])){
                                    $tmp['yuyue_sn'] = $v['orderNo'];
                                    $tmp['price'] = $v['price'];
                                    $finishTime = date('Y-m-d H-i-s', $v['add_time']);
                                    $tmp['finish_time'] = $finishTime;
                                }
                                break;
                            case "months":
                                $diff = $number*30*24*3600;
                                if( $diff >= (time() - $v['add_time'])){
                                    $tmp['yuyue_sn'] = $v['orderNo'];
                                    $tmp['price'] = $v['price'];
                                    $finishTime = date('Y-m-d H-i-s', $v['add_time']);
                                    $tmp['finish_time'] = $finishTime;
                                }
                                break;
                            default:
                                $diff = 7*24*3600;
                                if( $diff >= (time() - $v['add_time'])){
                                    $tmp['yuyue_sn'] = $v['orderNo'];
                                    $tmp['price'] = $v['price'];
                                    $finishTime = date('Y-m-d H-i-s', $v['add_time']);
                                    $tmp['finish_time'] = $finishTime;
                                }
                                break;
                        }
                        array_push($data, $tmp);
                    }
                $this->ret['data'] = array("orders"=>$data);
                $this->ret['msg'] = "OK";
                $this->ret['code'] = 0;
                }
            }else{
                $this->setRet(array('code'=>PARAM_ERR));
            }
    }




}
