<?php
/**
 * Created by PhpStorm.
 * User: 肖喜进
 * Date: 2016/7/19
 * Time: 17:37
 */
namespace Module\Ucenter\Service;


class Member extends \Xphp\Service
{

    public function getHeadMessage($params){

        return service('test');
    }

    public function test($params){
        return $params;
    }
}
