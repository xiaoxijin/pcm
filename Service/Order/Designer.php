<?php
/**
 * Created by PhpStorm.
 * User: 肖喜进
 * Date: 2016/7/19
 * Time: 17:37
 */
namespace Service\Order;


class Designer
{

    public function list($params){
//        pushFailedMsg("NO_RESULT");
//        return false;
        return service('ucenter/member/test',$params);
    }

    public function test($params){

        return json_encode($params)."designer_test";
    }

}
