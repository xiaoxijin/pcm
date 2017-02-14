<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/2/14
 * Time: 10:46
 */

namespace IFace;

interface Http {

    function onRequest($request,$response);

//    function header($k, $v);
//
//    function status($code);
//
//    function response($content);
//
//    function redirect($url, $mode = 301);
//
//    function finish($content = null);
//
//    function setcookie($name, $value = null, $expire = null, $path = '/', $domain = null, $secure = null,
//                       $httponly = null);

}
