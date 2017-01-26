<?php
/**
 * Created by PhpStorm.
 * User: 肖喜进
 * Date: 2016/7/20
 * Time: 15:35
 */

namespace Module\Ucenter\Models;
use \Module\Ucenter\Model as Model;


class Member_like extends Model
{
    public $table = 'member_like';
    public $primary = 'like_id';
}

