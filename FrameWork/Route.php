<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/1/26
 * Time: 15:57
 */

namespace Xphp;


class Route
{

    static public function getRequestInfo($params){
        if(!isset($params['api']))
            return false;
        $name_arr = explode("/",$params['api']['name']);
        if(count($name_arr)!=3){
            return false;
        }
        $route['act']=$name_arr[2];
        unset($name_arr[2]);
        $route['ctl']=join("/",$name_arr);
        $route['act_params']= $params['api']['params'];
        return $route;
    }
}