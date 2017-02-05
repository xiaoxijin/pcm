<?php
/**
 * Created by PhpStorm.
 * User: 肖喜进
 * Date: 2016/7/19
 * Time: 17:37
 */
namespace Module\Ucenter\Service;


class Member extends \Xphp\DataService
{

    public function getHeadMessage($params){
//        pushFailedMsg("NO_RESULT");
//        return false;
        return [
            'test1'=>service('order/designer/list',$params),
            'test2'=>service('test','hello word.')
        ];
    }

    public function test($params){
//        pushFailedMsg("AUTOLOAD_NOT_FOUNT");
        return service('test1',$params);
    }


    public function test1($params){
//        pushFailedMsg("AUTOLOAD_NOT_FOUNT");
        return 'member_test1'.json_encode($params);
    }


}
