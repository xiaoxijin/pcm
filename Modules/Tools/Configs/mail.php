<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/7/27
 * Time: 18:16
 */


$mail['master']= array(
    'mode'       => 'smtp',
    'smtp'       => array('host'=>'smtp.exmail.qq.com',
                          'port'=>'25',
                          'uname'=>'services@jcy.cc',
                          'passwd'=>'Jcy10241024'),
    'sender'       => 'services@jcy.cc',
    'receiver'       => 'xiaoxijin23@126.com',
    'title'       => '家创易',

);

return $mail;