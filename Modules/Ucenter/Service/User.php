<?php
/**
 * Created by PhpStorm.
 * User: 肖喜进
 * Date: 2016/7/19
 * Time: 17:37
 */
namespace Module\Ucenter\Service;


class User extends \Xphp\DataService
{

    public function getHeadMessage($params){
//        pushFailedMsg("NO_RESULT");
//        return false;
//        return $this->get($params['member_id']);
//        return $this->get(['mobile'=>'13240029857']);
        return $this->del(2);
    }

}
