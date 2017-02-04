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
//        setRet("NO_RESULT");
//        return false;
        return [
            'test1'=>service('order/designer/list','123123123'),
            'test2'=>service('test','hello word.')
        ];


    }

    public function test($params){
//        setRet("AUTOLOAD_NOT_FOUNT");
        return $params;
    }

}
