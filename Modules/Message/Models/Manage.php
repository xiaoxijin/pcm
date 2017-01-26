<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/7/24
 * Time: 13:29
 */

namespace Module\Message\Models;
use \Module\Message\Model as Model;
use Xphp\Tool;

class Manage extends Model
{
    public $table = 'message';
    public $primary='message_id';


    public function __format_row_data($row){

        if(Tool::isValid($row['send_id']))
            $row['from']=model("ucenter/member")->get($row['send_id'])['realname'];
        else
            $row['from']='system';
        unset($row['send_id']);
        $row['read'] = $row['state'];
        unset($row['state']);
        $row['time'] = date('Y-m-d H:i:s', $row['time']);
        $row['short_time'] = date('m-d', $row['time']);
        return $row;
    }
}