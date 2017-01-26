<?php
/**
 * Created by PhpStorm.
 * User: 肖喜进
 * Date: 2016/7/20
 * Time: 15:35
 */

namespace Module\Ucenter\Models;
use \Module\Ucenter\Model as Model;



class Designer extends Model
{

    public $table = 'designer';
    public $primary = 'uid';

    public function get_user_info($uid){

        $userinfo = $this->get($uid);
        return $userinfo;
    }
}

