<?php
/**
 *支付宝移动页面支付（手机网站支付接口）
 * Created by PhpStorm.
 * User: engin
 * Date: 2016/7/29
 * Time: 11:25
 */
$alipay_config= array(
    //合作身份者ID，签约账号，以2088开头由16位纯数字组成的字符串，查看地址：https://b.alipay.com/order/pidAndKey.htm
    'partner'    => "2088121914030971",
    //收款支付宝账号，以2088开头由16位纯数字组成的字符串，一般情况下收款账号就是签约账号
    'seller_id'=>"2088121914030971",
    // MD5密钥，安全检验码，由数字和字母组成的32位字符串，查看地址：https://b.alipay.com/order/pidAndKey.htm
    'key'=>"9uelhogquqzqxb82pffh4ufrcgfr0gpa",
    //签名方式
    'sign_type'=>strtoupper('MD5'),
    //字符编码格式 目前支持utf-8
    'input_charset'=>strtolower('utf-8'),
    //ca证书路径地址，用于curl中ssl校验
    //请保证cacert.pem文件在当前文件夹目录中
    'cacert'=>\Module\Pay\Controller::$CACERT_FILE,
//    'cacert'=>getcwd().\Module\Pay\Controller::$CACERT_FILE,
//    'cacert'=>getcwd().'\\cacert.pem',
    //访问模式,根据自己的服务器是否支持ssl访问，若支持请选择https；若不支持请选择http
    'transport'=>"http",
    // 支付类型 ，无需修改
    'payment_type'=>"1",
    // 产品类型，无需修改
    'service'=>"alipay.wap.create.direct.pay.by.user",
    'return_url'=>"http://testds.jcy.cc/user/paySuccess",
    "notify_url"	=> "http://testds.jcy.cc:9576/openapi/Pay/Alipaynotify/notify",

);
return $alipay_config;