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

        return $this->set(['mobile'=>'13240029857'],['mobile'=>'18899776965']);
//        return $this->get(['mobile'=>'13240029857']);
//        return $this->del(['mobile'=>'13240029857']);
    }

}
