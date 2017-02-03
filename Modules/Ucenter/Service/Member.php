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
        return service('test','hello word.');
    }


    public function test($params){
        throw new \Exception("AUTOLOAD_NOT_FOUNT");
    }

}
