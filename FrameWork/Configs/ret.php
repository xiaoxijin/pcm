<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/7/23
 * Time: 14:59
 * code一共六位 例如 100001 参数校验错误 前三位 子类编号， 后三位 指明子类中状态码的编码
 * 头三位100以内，为os,server,xphp的code
 * 头三位100开始，为用户类code
 * 头三位200开始，为没有数据产生的错误
*/

$ret = array(
//    'AUTHCODE_NOTMATCH'       => array('code'=>101010,'msg'=>'验证码不匹配'),
//    'AUTHCODE_OVERTIME'       => array('code'=>101009,'msg'=>'验证码已超时'),//验证码已经超时
//    'AUTHCODE_ERROR'       => array('code'=>101001,'msg'=>'验证码错误'),
//    'AUTHCODE_EMPTY'       => array('code'=>101002,'msg'=>'验证码不能为空'),
    'OK'                  => array('code'=>0,'msg'=>'OK'),
    'TRANSFER_SUCCESS'    => array('code'=>0,'msg'=>'异步任务已经成功投递'),
//    'DESIGNER_CERT_ERROR'  => array('code'=>101003,'msg'=>'非设计师用户，不能登录'),
    'Failed'  => array('code'=>100001,'msg'=>'没有数据'),

    'NO_RESULT'  => array('code'=>100002,'msg'=>'没有数据'),
    'AUTOLOAD_NOT_FOUNT'  => array('code'=>100003,'msg'=>'自动加载类没有找到'),
    'PARAM_ERR' => array('code'=>10004,'msg'=>'参数不正确'),//请求接口是传入的参数有误params
    'NOT_FOUNT_BOOTSTRAP'  => array('code'=>100005,'msg'=>'没有找到指定的引用类'),
    'UNKNOW_TASK_TYPE'  => array('code'=>100006,'msg'=>'未知的任务类型'),
    'UNKNOW_CMD'  => array('code'=>100006,'msg'=>'未知的命令'),
    'PACKET_LENGTH_INVALID'  => array('code'=>100007,'msg'=>'包长度非法'),
//    'NO_ACCEPT_CITY_ID'  => array('code'=>200001,'msg'=>'当前设计师接单范围不明'),
//    'VERIFY_MAIL_ERROR'  => array('code'=>101007,'msg'=>'邮箱未认证'),
//    'VERIFY_CASE_ERROR'  => array('code'=>101008,'msg'=>'您没有审核通过的案例'),

//    'DESIGNER_COMPETE_ERROR' => array('code'=>101004,'msg'=>'抢单失败'),
//    'DESIGNER_ORDER_PAYED' => array('code'=>101005,'msg'=>'此订单已付款'),
//    'DESIGNER_ORDER_PAY_OVERTIME' => array('101006'=>101001,'msg'=>'此订单已超出付款时间'),

);

return $ret;
