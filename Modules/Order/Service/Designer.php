<?php
/**
 * Created by PhpStorm.
 * User: 肖喜进
 * Date: 2016/7/19
 * Time: 17:37
 */
namespace Module\Order\Service;


class Designer
{

    public function list($params){
        pushMsg("NO_RESULT");
//        return false;
        return service('ucenter/member/test',$params);
    }

    public function test($params){
//        setRet("AUTOLOAD_NOT_FOUNT");
        return json_encode($params)."designer_test";
    }

}
