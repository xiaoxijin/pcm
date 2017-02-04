<?php
/**
 * Created by PhpStorm.
 * User: 肖喜进
 * Date: 2016/7/19
 * Time: 17:37
 */
namespace Module\Order\Service;


class Designer extends \Xphp\DataService
{

    public function list($params){
//        setRet("NO_RESULT");
//        return false;
        return service('test',$params);

    }

    public function test($params){
//        setRet("AUTOLOAD_NOT_FOUNT");
        return $params."designer_test";
    }

}
