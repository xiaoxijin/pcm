<?php
/**
 * Created by PhpStorm.
 * User: 肖喜进
 * Date: 2016/7/19
 * Time: 17:37
 */
namespace Service\Ucenter;


class User extends \DataService
{

    public function getHeadMessage($uid){

        return $this->set($uid,['mobile'=>'13240029857']);
//        return $this->get(['mobile'=>'13240029857']);
//        return $this->del(['mobile'=>'13240029857']);
    }

}
